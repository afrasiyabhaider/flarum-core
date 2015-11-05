<?php
/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Install\Controller;

use Flarum\Core\User;
use Flarum\Http\Controller\ControllerInterface;
use Flarum\Http\Session;
use Flarum\Http\WriteSessionCookieTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response;
use Flarum\Install\Console\InstallCommand;
use Flarum\Install\Console\DefaultsDataProvider;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;
use Illuminate\Contracts\Bus\Dispatcher;
use Exception;
use DateTime;

class InstallController implements ControllerInterface
{
    use WriteSessionCookieTrait;

    protected $command;

    /**
     * @var Dispatcher
     */
    protected $bus;

    public function __construct(InstallCommand $command, Dispatcher $bus)
    {
        $this->command = $command;
        $this->bus = $bus;
    }

    /**
     * @param Request $request
     * @param array $routeParams
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Request $request, array $routeParams = [])
    {
        $input = $request->getParsedBody();

        $data = new DefaultsDataProvider;

        $data->setDatabaseConfiguration([
            'driver'   => 'mysql',
            'host'     => array_get($input, 'mysqlHost'),
            'database' => array_get($input, 'mysqlDatabase'),
            'username' => array_get($input, 'mysqlUsername'),
            'password' => array_get($input, 'mysqlPassword'),
            'prefix'   => array_get($input, 'tablePrefix'),
        ]);

        $data->setAdminUser([
            'username'              => array_get($input, 'adminUsername'),
            'password'              => array_get($input, 'adminPassword'),
            'password_confirmation' => array_get($input, 'adminPasswordConfirmation'),
            'email'                 => array_get($input, 'adminEmail'),
        ]);

        $baseUrl = rtrim((string) $request->getAttribute('originalUri'), '/');
        $data->setBaseUrl($baseUrl);

        $data->setSetting('forum_title', array_get($input, 'forumTitle'));
        $data->setSetting('mail_from', 'noreply@' . preg_replace('/^www\./i', '', parse_url($baseUrl, PHP_URL_HOST)));
        $data->setSetting('welcome_title', 'Welcome to ' . array_get($input, 'forumTitle'));

        $body = fopen('php://temp', 'wb+');
        $input = new StringInput('');
        $output = new StreamOutput($body);

        $this->command->setDataSource($data);

        try {
            $this->command->run($input, $output);
        } catch (Exception $e) {
            return new HtmlResponse($e->getMessage(), 500);
        }

        $session = Session::generate(User::find(1), 60 * 24 * 14);
        $session->save();

        return $this->addSessionCookieToResponse(new Response($body, 200), $session, 'flarum_session');
    }
}
