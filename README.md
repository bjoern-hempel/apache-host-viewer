# An apache host viewer

An apache host viewer to list all provided host and vhosts on current system.

## A.) Installation

Create a directory and clone the git repository into:

```
user$ mkdir ~/apache-host-viewer && cd ~/apache-host-viewer
user$ git clone git@github.com:bjoern-hempel/apache-host-viewer.git .
```

## 1.) Usage

### 1.1) Show the help dialog (`--help`)

```
user$ ~/apache-host-viewer/bin/apache-host-viewer --help

Usage: bin/apache-host-viewer [options...]
 -h,    --help                    Shows this help.

 -m,    --markdown                Shows the output as markdown and disable all other output types (json, html) - default output
 -j,    --json                    Shows the output as json and disable all other output types (markdown, html)
        --html                    Shows the output as html and disable all other output types (markdown, json)
        --compress-json           Compress the outputted json.

        --output-target           Writes the result into this target folder.
        --output-name             Set the output name (default is "index").
        --create-markdown         Creates a markdown file (--output-target must be set).
        --create-json             Creates a json file (--output-target must be set).
        --create-html             Creates a html file (--output-target must be set).

 -s,    --show-system-info        Shows system informations.

 -d,    --docker-container        Checks a docker machine instead of the local machine.

        --disable-vhost-parser    Disable the vhost parser.

        --silence                 Disable the output.
```

### 1.2) Simply get all DocumentRoots as markdown markup language

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer
```

The markdown result could be:

```
### Projects

- file:///var/www/de/rsm-live/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-live.de
    - http://start-up-bw.wmbw.rsm-live.de
    - https://www.start-up-bw.wmbw.rsm-live.de
    - https://start-up-bw.wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- file:///var/www/html
  - targets:
    - http://wmbw.rsm-live.de
    - http://www.wmbw.rsm-live.de
    - http://wmbw.rsm-live.de
    - https://www.wmbw.rsm-live.de
    - https://wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- https://www.startupgipfel.de/registration/
  - targets:
    - http://www.start-up-bw.de
    - http://start-up-bw.de
    - http://www.startupbw.de
    - http://startupbw.de
  - ssl: false
  - app: redirection
  - version: not available
- file:///var/www/de/rsm-stage/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-stage.de
    - http://start-up-bw.wmbw.rsm-stage.de
    - https://www.start-up-bw.wmbw.rsm-stage.de
    - https://start-up-bw.wmbw.rsm-stage.de
  - ssl: true
  - app: TYPO3
  - version: 8.7.1
```

### 1.3) HTML output (`--html`)

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --html
```

The markdown result could be:

```
<!DOCTYPE html>
<html lang="en">
    <head>
        <!--[if IE]>                                                                                                                                                   
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta charset="UTF-8" />
        <title>wmbw.rsm-live.de</title>
        <link rel="stylesheet" href="markdown.css">
        <style>
            .markdown-body {
                box-sizing: border-box;
                min-width: 200px;
                max-width: 980px;
                margin: 0 auto;
                padding: 45px;
            }
        </style>
    </head>
    <body>
        <article class="markdown-body">
<h3 id="projects">Projects</h3>
<ul>
<li>file:///var/www/de/rsm-live/wmbw/start-up-bw/www/html/current/web<ul>
<li>targets:<ul>
<li><a href="http://www.start-up-bw.wmbw.rsm-live.de">http://www.start-up-bw.wmbw.rsm-live.de</a></li>
<li><a href="http://start-up-bw.wmbw.rsm-live.de">http://start-up-bw.wmbw.rsm-live.de</a></li>
<li><a href="https://www.start-up-bw.wmbw.rsm-live.de">https://www.start-up-bw.wmbw.rsm-live.de</a></li>
<li><a href="https://start-up-bw.wmbw.rsm-live.de">https://start-up-bw.wmbw.rsm-live.de</a></li>
</ul>
</li>
<li>ssl: true</li>
<li>app: HTML project</li>
<li>version: unknown</li>
</ul>
</li>
<li>file:///var/www/html<ul>
<li>targets:<ul>
<li><a href="http://wmbw.rsm-live.de">http://wmbw.rsm-live.de</a></li>
<li><a href="http://www.wmbw.rsm-live.de">http://www.wmbw.rsm-live.de</a></li>
<li><a href="http://wmbw.rsm-live.de">http://wmbw.rsm-live.de</a></li>
<li><a href="https://www.wmbw.rsm-live.de">https://www.wmbw.rsm-live.de</a></li>
<li><a href="https://wmbw.rsm-live.de">https://wmbw.rsm-live.de</a></li>
</ul>
</li>
<li>ssl: true</li>
<li>app: HTML project</li>
<li>version: unknown</li>
</ul>
</li>
<li><a href="https://www.startupgipfel.de/registration/">https://www.startupgipfel.de/registration/</a><ul>
<li>targets:<ul>
<li><a href="http://www.start-up-bw.de">http://www.start-up-bw.de</a></li>
<li><a href="http://start-up-bw.de">http://start-up-bw.de</a></li>
<li><a href="http://www.startupbw.de">http://www.startupbw.de</a></li>
<li><a href="http://startupbw.de">http://startupbw.de</a></li>
</ul>
</li>
<li>ssl: false</li>
<li>app: redirection</li>
<li>version: not available</li>
</ul>
</li>
<li>file:///var/www/de/rsm-stage/wmbw/start-up-bw/www/html/current/web<ul>
<li>targets:<ul>
<li><a href="http://www.start-up-bw.wmbw.rsm-stage.de">http://www.start-up-bw.wmbw.rsm-stage.de</a></li>
<li><a href="http://start-up-bw.wmbw.rsm-stage.de">http://start-up-bw.wmbw.rsm-stage.de</a></li>
<li><a href="https://www.start-up-bw.wmbw.rsm-stage.de">https://www.start-up-bw.wmbw.rsm-stage.de</a></li>
<li><a href="https://start-up-bw.wmbw.rsm-stage.de">https://start-up-bw.wmbw.rsm-stage.de</a></li>
</ul>
</li>
<li>ssl: true</li>
<li>app: TYPO3</li>
<li>version: 8.7.1</li>
</ul>
</li>
</ul>
</article>
    </body>
</html>
```

### 1.4) JSON output (`--json`)

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --json
```

The markdown result could be:

```
{
    "projects": {
        "file:///var/www/de/rsm-live/wmbw/start-up-bw/www/html/current/web": {
            "domains": [
                "http://www.start-up-bw.wmbw.rsm-live.de",
                "http://start-up-bw.wmbw.rsm-live.de",
                "https://www.start-up-bw.wmbw.rsm-live.de",
                "https://start-up-bw.wmbw.rsm-live.de"
            ],
            "ssl": true,
            "app": "HTML project",
            "version": "unknown"
        },
        "file:///var/www/html": {
            "domains": [
                "http://wmbw.rsm-live.de",
                "http://www.wmbw.rsm-live.de",
                "http://wmbw.rsm-live.de",
                "https://www.wmbw.rsm-live.de",
                "https://wmbw.rsm-live.de"
            ],
            "ssl": true,
            "app": "HTML project",
            "version": "unknown"
        },
        "https://www.startupgipfel.de/registration/": {
            "domains": [
                "http://www.start-up-bw.de",
                "http://start-up-bw.de",
                "http://www.startupbw.de",
                "http://startupbw.de"
            ],
            "ssl": false,
            "app": "redirection",
            "version": "not available"
        },
        "file:///var/www/de/rsm-stage/wmbw/start-up-bw/www/html/current/web": {
            "domains": [
                "http://www.start-up-bw.wmbw.rsm-stage.de",
                "http://start-up-bw.wmbw.rsm-stage.de",
                "https://www.start-up-bw.wmbw.rsm-stage.de",
                "https://start-up-bw.wmbw.rsm-stage.de"
            ],
            "ssl": true,
            "app": "TYPO3",
            "version": "8.7.1"
        }
    }
}
```

### 1.5) Add some additional system informations (`--show-system-info`)

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --show-system-info
```

The markdown result could be:

```
### System informations

| Name | Value |
| ---- | ----- |
| document created at | 2017-05-28 16:38:38 |
| full os name | Linux Debian 8.8 (3.16.0-4-amd64 x86_64) |
| number of cpus | 4 |
| ram size in gb | 15.6774 |
| hd disc size in gb | 219 |
| used hd disc size | 2 % |
| number of updateable applications | 0 |
| php version | 7.0.19-1~dotdeb+8.1 |
| mysql version | 5.5.55 |

### Users

| Username | Fullname |
| ---- | ----- |
| bjoern | Björn Hempel |
| user2 | User 2 |
| user3 | User 3 |

### Projects

- file:///var/www/de/rsm-live/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-live.de
    - http://start-up-bw.wmbw.rsm-live.de
    - https://www.start-up-bw.wmbw.rsm-live.de
    - https://start-up-bw.wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- file:///var/www/html
  - targets:
    - http://wmbw.rsm-live.de
    - http://www.wmbw.rsm-live.de
    - http://wmbw.rsm-live.de
    - https://www.wmbw.rsm-live.de
    - https://wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- https://www.startupgipfel.de/registration/
  - targets:
    - http://www.start-up-bw.de
    - http://start-up-bw.de
    - http://www.startupbw.de
    - http://startupbw.de
  - ssl: false
  - app: redirection
  - version: not available
- file:///var/www/de/rsm-stage/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-stage.de
    - http://start-up-bw.wmbw.rsm-stage.de
    - https://www.start-up-bw.wmbw.rsm-stage.de
    - https://start-up-bw.wmbw.rsm-stage.de
  - ssl: true
  - app: TYPO3
  - version: 8.7.1
```

You can use this parameter in combination with all other parameters (--markdown, --json, --html, --create-markdown, --create-json, --create-html).

### 1.6) Create md file into a given folder (`--output-target`)

The following command will create a markdown file (`index.md`) into the folder `/var/ww/html/server`. The following command also prints the markdown result on the screen:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --output-target=/var/www/html/server --output-name=index
```

### 1.7) What's with json and html files? (`--create-json` and `--create-html`)

Here it comes. It will also create a `index.html` and a `index.json` file into the folder `/var/ww/html/server`:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --output-target=/var/www/html/server --output-name=index --create-json --create-html
```

### 1.8) Suppress the default output (`--silence`)

You can suppress all outputs (except error messages) with the parameter `--silence`. This is usefull if you like to use this command as a cronjob task. The following command creates a md, a json and a html file into the folder `/var/www/html/server` and don't output any message to the command line:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer --output-target=/var/www/html/server --output-name=index --show-system-info --show-links --create-json --create-html --silence
```

### 1.9) Check docker container instead of local system

TODO..

## B.) License

MIT © [Björn Hempel](https://www.ixno.de)

Have fun! :)
