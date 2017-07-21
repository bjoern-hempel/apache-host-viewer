<?php

/**
 * An apache host viewer php helper class.
 *
 * @author  Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2017-07-21)
 */
class ApacheHostViewer
{
    /**
     * An initializer.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function initialize()
    {   
        /* set timezone */
        date_default_timezone_set('Europe/Berlin');
    }

    /**
     * The apache host viewer controller.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function controller()
    {
        self::initialize();

        /* get some uri specific datas */
        $requestUri    = $_SERVER['REQUEST_URI'];
        $requestedFile = basename($requestUri);

        /* do some uri specific datas */
        switch ($requestedFile) {
            case 'live.json':
                ApacheHostViewer::printJsonLiveValues();
                break;

            case 'update.json':
                ApacheHostViewer::printJsonUpdateStatus();
                break;

            default:
                ApacheHostViewer::printJsonMessageStatus("Unknown requested file \"$requestedFile\".", 'failed');
                break;
        }
    }

    /**
     * Returns the live.json.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getLiveValues()
    {
        /* collect some informations */
        $load         = sys_getloadavg();
        $time         = time();
        $timeFormated = date('Y-m-d H:i:s');
        $appName      = exec('friends-of-bash version apache-host-viewer');
        $osName       = exec('friends-of-bash osName');

        $response = array();

        $response = array_merge(
            $response,
            array(
                'timezone'            => date_default_timezone_get(),
                'created-at-formated' => $timeFormated,
                'created-at'          => $time,
                'created-by'          => $appName,
                'os-name-full'        => $osName,
                'load-average'        => array(
                    1  => $load[0],
                    5  => $load[1],
                    15 => $load[2],
                ),
            )
        );

        /* return the response object */
        return $response;
    }

    /**
     * Returns a status object including a given message.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getMessageStatus($message, $status = 'ok', Array $array = array())
    {
        $time         = time();
        $timeFormated = date('Y-m-d H:i:s');

        return array_merge(
            $array,
            array(
                'timezone'            => date_default_timezone_get(),
                'created-at-formated' => $timeFormated,
                'created-at'          => $time,
                'status'              => $status,
                'message'             => $message,
            )
        );
    }

    /**
     * Gets the apache-host-viewer update command.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateCommand()
    {
        /* the apache-host-viewer command name */
        $apacheHostViewerCommand = 'apache-host-viewer';

        $apacheHostViewerUpdateCommandDefault = 'apache-host-viewer '.
                                                '--output-target=/var/www/html/server '.
                                                '--output-name=index '.
                                                '--show-system-info '.
                                                '--show-links '.
                                                '--show-ssl-certificates '.
                                                '--show-domain-list '.
                                                '--create-json '.
                                                '--create-html '.
                                                '--silence';

        $apacheHostViewerUpdateCommand = exec(
            sprintf(
                'cat /etc/crontab | grep -v "^#" | grep "%s" | sed -e "s/^.*%s/%s/g"',
                $apacheHostViewerCommand,
                $apacheHostViewerCommand,
                $apacheHostViewerCommand
            )
        );

        if ($apacheHostViewerUpdateCommand === '') {
            $apacheHostViewerUpdateCommand = $apacheHostViewerUpdateCommandDefault;
        }

        return sprintf('%s %s', 'sudo', $apacheHostViewerUpdateCommand);
    }

    /**
     * Updates the apache host viewer data base.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateStatus()
    {
        /* the command itself */
        $command = self::getUpdateCommand();

        /* the working directory */
        $cwd = '/opt/friends-of-bash';

        /* some environment variables */
        $env = array();

        /* pipe input */
        $stdin = null;

        /* the pipe config (read input, write stdout and stderr */
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        /* user */
        $userid = posix_geteuid();
        $user   = posix_getpwuid(posix_geteuid());

        /* open the command */
        $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

        /* stdout & stderr & retval */
        $stdout = null;
        $stderr = null;
        $retval = 0;

        /* resource is not available */
        if (!is_resource($process)) {
            $hints = <<<HINTS
<p>
    An error occurred. :(
</p>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;
        
            return self::getMessageStatus(
                'An error occurred, while trying to do the update. The proc_open ressource could not be opened.',
                'failed',
                array(
                    'command' => $command,
                    'hints'   => $hints,
                )
            );
        }

        /* ressource is available */
        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($process);

        /* an error occurred */
        if ($retval > 0) {
            $hints = <<<HINTS
<p>
    The command was not executed successfully.
    Have you allowed the script "apache-host-viewer" to user "%s" (visudo)?
    Please try something like this:
</p>

<pre>
sudo vi /etc/sudoers
</pre>

<pre>
# some scripts www-data is allowed to execute as www-data
www-data ALL=NOPASSWD:/usr/local/bin/apache-host-viewer
</pre>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;

            return self::getMessageStatus(
                sprintf('The command was not executed successfully and returns the status code %d.', $retval),
                'failed',
                array(
                    'command' => $command,
                    'hints'   => sprintf($hints, $user['name']),
                )
            );
        }

        $hints = <<<HINTS
<p>
    The apache host viewer data base was successfully updated.
</p>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;

        return self::getMessageStatus(
            'The apache host viewer data base was successfully updated.',
            'success',
            array(
                'command' => $command,
                'hints'   => $hints,
            )
        );
    }

    /**
     * Prints the given data object as json object. Also adds the json header.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJson($data)
    {
        header('Content-Type: application/json');

        echo json_encode($data);
    }

    /**
     * Prints the live values.
     *
     * @version 1.0 (2017-07-21)
     */ 
    public static function printJsonLiveValues()
    {
        self::printJson(self::getLiveValues());
    }

    /**
     * Prints the update status.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJsonUpdateStatus()
    {
        self::printJson(self::getUpdateStatus());
    }

    /**
     * Prints out the message status.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJsonMessageStatus($message, $status = 'ok', Array $array = array())
    {
        self::printJson(self::getMessageStatus($message, $status, $array));
    }
}

/* start the apache host viewer controller */
ApacheHostViewer::controller();

