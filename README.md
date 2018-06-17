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

 - Docker
 - Docker Compose


### Container starten

Um die Anwendung zu starten, können durch Docker Compose einfach die benötigten Container gestartet
werden:

```shell
docker-compose up
```

Vorher sollten in `docker-compose.yml` die MySQL-Zugangsdaten in den Umgebungsvariablen geändert werden.


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
