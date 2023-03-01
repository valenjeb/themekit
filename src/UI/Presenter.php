<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use Brain\Hierarchy\Hierarchy;
use Devly\DI\Contracts\IContainer;
use Devly\Exceptions\AbortException;
use Devly\Exceptions\FileNotFoundException;
use Devly\ThemeKit\UI\Contracts\IPresenter;
use Devly\ThemeKit\UI\Contracts\ISignalReceiver;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Devly\Utils\Arr;
use Devly\WP\Models\User;
use Devly\WP\Routing\Contracts\IResponse;
use Devly\WP\Routing\Request;
use Devly\WP\Routing\Responses\RedirectResponse;
use Devly\WP\Routing\Responses\TextResponse;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidStateException;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use RuntimeException;
use stdClass;

use function array_map;
use function array_merge;
use function is_string;
use function ltrim;
use function preg_match;
use function rtrim;
use function sprintf;
use function strrpos;
use function substr;

abstract class Presenter extends Control implements IPresenter
{
    public const PRESENTER_KEY = 'presenter';
    public const SIGNAL_KEY    = 'do';

    /** @internal special parameter key */
    public const ACTION_KEY = 'action';

    /** @internal special parameter key */
    public const FLASH_KEY = '_fid';

    /** @internal special parameter key */
    public const DEFAULT_ACTION = 'default';
    public const MODE_PRINT     = 1;
    public const MODE_NO_PRINT  = 2;

    public static int $printMode = self::MODE_NO_PRINT;
    protected IContainer $container;
    protected Request $request;
    protected ?IResponse $response = null;
    protected HttpResponse $httpResponse;
    protected HttpRequest $httpRequest;
    /** @var array<string, mixed> */
    protected array $globalParams;
    /** @var false|string */
    protected string $signalReceiver;
    protected ?string $signal;
    /** @var array<callable(self): void>  Occurs when the presenter is rendering after beforeRender */
    protected array $onRender = [];
    protected ?Session $session;
    protected ?ITemplateFactory $templateFactory;
    protected ?User $user;
    private string $action;
    private string $view;
    protected stdClass $payload;

    /** @return IResponse|void */
    public function run(Request $request)
    {
        $this->request = $request;
        $this->payload = new stdClass();
        if (! $this->httpResponse->isSent()) {
            $this->httpResponse->addHeader('Vary', 'X-Requested-With');
        }

        $this->initGlobalParameters();

        try {
            // calls $this->action<Action>()
            $this->tryCall(static::formatActionMethod($this->action), $this->params);

            // autoload components
            foreach ($this->globalParams as $id => $foo) {
                $this->getComponent((string) $id, false);
            }

            $this->processSignal();

            // RENDERING VIEW
            $this->beforeRender();

            Arr::invoke($this->onRender, $this);

            // calls $this->render<View>()
            $this->tryCall(static::formatRenderMethod($this->view), $this->params);
            $this->afterRender();

            if (self::$printMode === self::MODE_PRINT) {
                $this->sendTemplate();
            }
        } catch (AbortException $e) {
        }

        if ($this->hasFlashSession()) {
            $this->getFlashSession()->setExpiration('30 seconds');
        }

        if ($this->response) {
            return $this->response;
        }

        $template = $this->ensureTemplateFile($template ?? $this->getTemplate());
        $this->container->instance('view.template', $template);
    }

    /** @throws RuntimeException */
    public function processSignal(): void
    {
        if ($this->signal === null) {
            return;
        }

        $component = $this->signalReceiver === ''
            ? $this
            : $this->getComponent($this->signalReceiver, false);
        if ($component === null) {
            throw new RuntimeException(sprintf(
                "The signal receiver component '%s' is not found.",
                $this->signalReceiver
            ));
        }

        if (! $component instanceof ISignalReceiver) {
            throw new RuntimeException(
                sprintf("The signal receiver component '%s' is not SignalReceiver implementor.", $this->signalReceiver)
            );
        }

        $component->signalReceived($this->signal);
        $this->signal = null;
    }

    /** @throws AbortException */
    public function sendTemplate(?ITemplate $template = null): void
    {
        $template = $this->ensureTemplateFile($template ?? $this->getTemplate());

        $this->sendResponse(new TextResponse($template));
    }

    protected function ensureTemplateFile(ITemplate $template): ITemplate
    {
        if ($template->getFile()) {
            return $template;
        }

        $files = $this->formatTemplateFiles();

        $located = locate_template($files);

        if (empty($located)) {
            throw new FileNotFoundException('Page not found. Missing template.');
        }

        $template->setFile($located);

        return $template;
    }

    final public function getRequest(): ?Request
    {
        return $this->request;
    }

    private function initGlobalParameters(): void
    {
        $this->globalParams = [];
        $selfParams         = [];

        $params = array_merge($this->request->getRoute()->getParameters(), $this->request->getQueryVars());

        $tmp = $this->request->getHttpRequest()->getPost('_' . self::SIGNAL_KEY);

        if ($tmp !== null) {
            $params[self::SIGNAL_KEY] = $tmp;
        }

        foreach ($params as $key => $value) {
            if (! preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+$)[a-z0-9_]+)$#Di', (string) $key, $matches)) {
                continue;
            }

            if (! $matches[1]) {
                $selfParams[$key] = $value;
            } else {
                $this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
            }
        }

        // init & validate $this->action & $this->view
        $action = $selfParams[self::ACTION_KEY] ?? self::DEFAULT_ACTION;
        if (! is_string($action) || ! Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*$#D')) {
            throw new RuntimeException('Action name is not valid.');
        }

        $this->changeAction($action);
        $this->signal = null;

        // init $this->signalReceiver and key 'signal' in appropriate params array
        $this->signalReceiver = $this->getUniqueId();
        if (isset($selfParams[self::SIGNAL_KEY])) {
            $param = $selfParams[self::SIGNAL_KEY];
            if (! is_string($param)) {
                throw new RuntimeException('Signal name is not string.');
            }

            $pos = strrpos($param, '-');
            if ($pos) {
                $this->signalReceiver = substr($param, 0, $pos);
                $this->signal         = (string) substr($param, $pos + 1);
            } else {
                $this->signalReceiver = $this->getUniqueId();
                $this->signal         = $param;
            }

            if ($this->signal === '') {
                $this->signal = null;
            }
        }

        $this->params = $selfParams;
    }

    /**
     * Returns self.
     */
    final public function getPresenter(): self
    {
        return $this;
    }

    final public function getPresenterIfExists(): self
    {
        return $this;
    }

    /** @deprecated */
    final public function hasPresenter(): bool
    {
        return true;
    }

    /**
     * Returns a name that uniquely identifies component.
     */
    public function getUniqueId(): string
    {
        return '';
    }

    /**
     * Changes current action.
     */
    public function changeAction(string $action): void
    {
        $this->action = $this->view = $action;
    }

    /**
     * Returns current view.
     */
    final public function getView(): string
    {
        return $this->view;
    }

    /**
     * Changes current view. Any name is allowed.
     *
     * @return static
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Formats action method name.
     */
    public static function formatActionMethod(string $action): string
    {
        return 'action' . $action;
    }

    /**
     * Formats render view method name.
     */
    public static function formatRenderMethod(string $view): string
    {
        return 'render' . $view;
    }

    protected function createTemplate(?string $class = null): ITemplate
    {
        return $this->getTemplateFactory()->create($this);
    }

    /*****************************************************
     * request serialization
     *****************************************************/

    /**
     * Stores current request to session.
     *
     * @return string key
     */
    public function storeRequest(string $expiration = '+ 10 minutes'): string
    {
        $session = $this->getSession('Nette.Application/requests');
        do {
            $key = Random::generate(5);
        } while (isset($session[$key]));

        $session[$key] = [$this->user ? $this->user->getId() : null, $this->request];
        $session->setExpiration($expiration, $key);

        return $key;
    }

    /**
     * Restores request from session.
     */
    public function restoreRequest(string $key): void
    {
        $session = $this->getSession('Nette.Application/requests');
        if (
            ! isset($session[$key])
            || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getID())
        ) {
            return;
        }

        $request = clone $session[$key][1];
        unset($session[$key]);
        $params                  = $request->getParameters();
        $params[self::FLASH_KEY] = $this->getFlashKey();
        $request->setParameters($params);

        $this->redirectUrl($this->requestToUrl($request));
    }

    /********************************************
     * Flash session
     ********************************************/

    private function getFlashKey(): ?string
    {
        $flashKey = $this->getParameter(self::FLASH_KEY);

        return is_string($flashKey) && $flashKey !== ''
            ? $flashKey
            : null;
    }

    /**
     * Checks if a flash session namespace exists.
     */
    public function hasFlashSession(): bool
    {
        $flashKey = $this->getFlashKey();

        return $flashKey !== null
            && $this->getSession()->hasSection('Nette.Application.Flash/' . $flashKey);
    }

    /**
     * Returns session namespace provided to pass temporary data between redirects.
     */
    public function getFlashSession(): SessionSection
    {
        $flashKey = $this->getFlashKey();
        if ($flashKey === null) {
            $this->params[self::FLASH_KEY] = $flashKey = Random::generate(4);
        }

        return $this->getSession('Nette.Application.Flash/' . $flashKey);
    }

    /********************* services ****************d*g**/

    public function injectDefault(
        IContainer $container,
        ITemplateFactory $templateFactory,
        HttpResponse $httpResponse,
        HttpRequest $httpRequest,
        ?Session $session,
        ?User $user = null
    ): void {
        $this->container       = $container;
        $this->httpResponse    = $httpResponse;
        $this->httpRequest     = $httpRequest;
        $this->session         = $session;
        $this->templateFactory = $templateFactory;
        $this->user            = $user;
    }

    final public function getHttpRequest(): HttpRequest
    {
        return $this->httpRequest;
    }

    final public function getHttpResponse(): HttpResponse
    {
        return $this->httpResponse;
    }

    /** @return Session|SessionSection */
    final public function getSession(?string $namespace = null)
    {
        if (! $this->session) {
            throw new InvalidStateException('Service Session has not been set.');
        }

        return $namespace === null
            ? $this->session
            : $this->session->getSection($namespace);
    }

    final public function getUser(): User
    {
        if (! $this->user) {
            throw new InvalidStateException('Service User has not been set.');
        }

        return $this->user;
    }

    final public function getTemplateFactory(): ITemplateFactory
    {
        if (! $this->templateFactory) {
            throw new InvalidStateException('Service TemplateFactory has not been set.');
        }

        return $this->templateFactory;
    }

    final public function forwardPresenter(string $presenter): void
    {
        $this->container->call([$presenter, 'run']);
    }

    protected function beforeRender(): void
    {
    }

    protected function afterRender(): void
    {
    }

    /** @return string[] */
    protected function formatTemplateFiles(): array
    {
        $hierarchy = new Hierarchy();
        $templates = $hierarchy->templates();
        $dirname   = $this->container->config('view.dirname', 'views');
        $extension = $this->container->config('view.extension', 'php');

        return array_map(static function ($template) use ($dirname, $extension) {
            return ltrim($dirname, '/') . '/' . $template . '.' . rtrim($extension, '.');
        }, $templates);
    }

    /** @throws AbortException */
    protected function redirectUrl(string $url, ?int $httpCode = null): void
    {
        if (! $httpCode) {
            $httpCode = $this->httpRequest->isMethod('post')
                ? HttpResponse::S303_PostGet
                : HttpResponse::S302_Found;
        }

        $this->sendResponse(new RedirectResponse($url, $httpCode));
    }

    /** @throws AbortException */
    protected function sendResponse(IResponse $response): void
    {
        $this->response = $response;

        $this->terminate();
    }

    /** @throws AbortException */
    protected function terminate(): void
    {
        throw new AbortException();
    }

    protected function requestToUrl(Request $request): string
    {
        return home_url($request->wp()->request);
    }
}
