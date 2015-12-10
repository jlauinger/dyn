# DynDNS API

![#SWAG passing](http://u.dropme.de/5274/28ef04/swag-passing.svg)

Eine PHP-Anwendung, die eine API bereitstellt, um ein eigenes DynDNS zu konfigurieren und zu betreiben.

## Deployment

### Source Code und Toolchain

Repository in einen Ordner klonen:

```shell
git clone https://github.com/jlauinger/dyn.git
```

Sicherstellen, dass die benötigten Tools vorhanden sind (Debian):

```shell
curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
```

### Abhängigkeiten auflösen

Alle Abhängigkeiten installieren :sparkles:

```shell
composer install
```

### Webserver

Einen Webserver konfigurieren, sodass PHP ausgeführt wird.

**nginx:**

```
listen [::]:443 ssl;

[...]

location ~ \.php$ {
  include snippets/fastcgi-php.conf;
  fastcgi_pass unix:/var/run/php5-fpm.sock;
}

index index.php;
location / {
  try_files $uri $uri.html $uri/ =404;
  rewrite ^/(.*)$ /index.php last;
}
```

Achtung: unbedingt ausschließlich HTTPS verwenden, da bei jeder POST-Anfrage ein `client_secret` übertragen wird. Erhält ein Angreifer dieses, kann er beliebige IP-Adressen für den Host setzen.

### Datenbank

Eine MySQL-Datenbank für die Anwendung erstellen, z.B. `dyn`. Einen Datenbankbenutzer, z.B. `dyn` erstellen und ihm alle Rechte auf die Datenbank gewähren. Dessen Zugangsdaten müssen in `.htconfig.php` konfiguriert werden.

```sql
CREATE DATABASE dyn;
CREATE USER 'dyn'@'localhost' IDENTIFIED BY 'changeme1';
GRANT ALL PRIVILEGES ON dyn.* TO 'dyn'@'localhost';
FLUSH PRIVILEGES;
```

Das Datenbankschema importieren: (eventuell Zugangsdaten anpassen):

```shell
mysql dyn -u dyn -p < sqlsync/dyn.sql
```

### Anwendungseinstellungen

Die Datei `.htconfig.php` aus der Vorlage erstellen:

```shell
cp .htconfig.php.template .htconfig.php
```

Darin die Zugangsdaten zur Datenbank konfigurieren:

```php
[...]
define('DB_HOST', 'localhost');
define('DB_NAME', 'dyn');
define('DB_USER', 'dyn');
define('DB_PASS', 'changeme1');
[...]
```


## Benutzung

Einen neuen DynDNS-Host in die Datenbank hinzufügen:

```sql
INSERT INTO hosts (hostname, description, client_secret) VALUES ("router01-da", "Router in Darmstadt", \
  "halte dies geheim, damit kann man die IP-Adresse setzen!");
```

Aktuelle IP-Adressen und Informationen aller Hosts abfragen:

```shell
curl https://dyn.your-server.example/
# JSON response
```

Eigene externe IP-Adresse ermitteln:

```shell
curl https://dyn.your-server.example/ip
# direct text response
```

Neuen Wert für die eigene IP-Adresse setzen (POST):

```
curl -H "Content-Type: application/json" \
     -X POST \
     -d '{"hostname":"router01-da","ip":"10.0.1.1","client_secret":"you know it"}' \
     https://dyn.your-server.example/
# JSON response, success / error
```


## Beispielkonfiguration

Einen Cron-Job einrichten, um alle fünf Minuten die IP-Adresse zu aktualisieren:  
`/etc/crontab`

```
# run DynDNS reporter every five minutes
*/5 *   * * *   root    /usr/local/bin/dyndns-updater.sh
```

Shell-Skript, welches die externe Adresse lädt und der API mitteilt. Hier müssen URL, Hostname und `client_secret` noch angepasst werden:  
`/usr/local/bin/dyndns-updater.sh`

```
#!/bin/bash

# configure client_secret and hostname
HOSTNAME=homer
CLIENT_SECRET="you still know it, right?"

# fetch current external IP address
IP=`curl https://dyn.your-server.example/ip`

# report IP address to API
curl -H "Content-Type: application/json" \
     -X POST \
     -d '{"hostname":"'$HOSTNAME'","ip":"'$IP'","client_secret":"'$CLIENT_SECRET'"}' \
     https://dyn.your-server.example/
```


## TODO

 * korrekte HTTP Antwortcodes
 * besserer Mechanismus statt des `client_secret`?
 * DNS-Server konfigurieren, sodass er Subdomains automatisch anbietet


## Lizenz

Copyright 2015 Johannes Lauinger  
Bereitgestellt unter der GNU GPL, Version 3
