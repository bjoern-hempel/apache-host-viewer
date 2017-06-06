<?php

    /* the command itself */
    $command='sudo apache-host-viewer --output-target=/var/www/html/server --output-name=index --show-system-info --show-links --create-json --create-html --silence';

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

    # open the command
    $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

    # stdout & stderr & retval
    $stdout = null;
    $stderr = null;
    $retval = 0;

    # resource is available */
    if (is_resource($process)) {
        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($process);
    } else {
        echo 'An error occurred. :(<br />'."\n";
        echo '<br />'."\n";
        echo '<a href="/server">Go back to server page.</a>'."\n";
        exit;
    }

    /* an error occurred */
    if ($retval > 0) {
        $output = <<<EOL
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
EOL;

        echo sprintf($output, $user['name']);;

        exit;
    }

    echo 'The server page was successfully refreshed.<br />'."\n";
    echo '<br />'."\n";
    echo '<a href="/server">Go back to server page.</a>'."\n";

