# de.systopia.mutualaid - Corona Hilfe CiviCRM Erweiterung
## Einführung
Die Coronakrise und die damit einhergehende Schutzmaßnahmen wie soziale Distanzierung und Quarantäne stellen uns alle vor große Herausforderungen. Wir alle müssen Bedürftigen Menschen helfen und CiviCRM ist die optimale Plattform diese Hilfe zu organisieren und Hilfsbedürftige mit Freiwilligen in verbindung zu setzen.

## Ziele und Funktionen
Diese CiviCRM Erweiterung ermöglicht Organisationen, Hilfesuchende und Helfende miteinander in Verbindung zu setzen. Sie stellt konfigurierbare Onlineformulare für Hilfesuchende und Helfende zur Verfügung und verfügt über einen Algorhytmus, der diese Personen basierend auf räumlicher Nähe und weiteren Faktoren miteinander verknüpft. Die wichtigsten Funktionen sind: 

* zwei konfigurierbare Onlineformulare für Helfende und Hilfesuchende
* konfigurierbare Bestätigungs-E-Mails nach dem Ausfüllen der Formulare
* Nutzung von CiviCRM's eingebauten Geocoding Funktionen
* ein Zuordnungsalgorhytmus, der Hilfesuchende mit Helfenden in CiviCRM mit einer Beziehung verknüpft
* vorkonfigurierte Berichte um Zuordnungen zu finden, ggf. zu prüfen und zu bearbeiten
* erweitertes Matching von Kontakten mit Hilfe der Erweiterung [Extended Contact Matcher Extension](https://github.com/systopia/de.systopia.xcm) inklusive eines vorkonfigurierten Profils

## Installation and Prerequisites
Du benötigst eine aktuelle CiviCRM Version und Administratorberechtigungen. Installiere die Erweiterung auf die übliche Art, Informationen dazu findest Du [hier](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension). Die Erweiterung hat eine Abhängigkeit mit der [Extended Contact Matcher Erweiterung](https://github.com/systopia/de.systopia.xcm) sie sollte automatisch mit installiert werden wenn Du die Corona Hilfe Erweiterung für CiviCRM installierst.

Die Geocodingfunktion muss in CiviCRM konfiguriert sein - Informationen dazu findest Du [hier](https://docs.civicrm.org/user/en/latest/initial-set-up/mapping/). Weiterhin sollte die Berichtskomponente (CiviReport) aktiviert seinn. Alle relevanten CiviCRM-User benötigen die entsprechenden Berechtigungen, bspw. um Kontakte zu sehen und zu bearbeiten, auf Berichte zuzugreifen etc.


## Konfiguration
1. Navigiere zur Konfigurationsseite (Nachbarschaftshilfe >Konfiguration) und konfiguriere die Felder für die Onlineformulare inkl Standardwerte etc.
2. Falls Du eine Bestätigungsemail versenden möchtest, richte eine E-Mail-Vorlage ein und wähle sie auf der Konfigurationsseite aus
3. Gib einen Text für die Datenschutzbestimmungen ein, der in Deinen Onlineformularen eingeblendet wird.
4. Verlinke auf Deine Onlineformulare mithilfe der statischen URLs für das "ich benötige Hilfe" und das "ich kann Hilfe anbieten" Formular
5. Aktiviere bzw. konfiguriere den Cronjob der den Zuordnungs-Algorhytmus ausführt

* [Optional] Falls Du Hilfekategorien im Formular zur Verfügung stellen möchtest, konfiguriere diese in der CiviCRM Optionsgruppe "..."
* [Optional] Lege eine Landing Page auf Deinem CiviCRM-Content Management-System an
* [Optional] Passe die Zuordnungsregeln der Erweiterung Extended Contact Matcher Extension an
* [Optional] Passe die von der Erweiterung angelegten Berichte an


## Beschreibung und Nutzung
Immer wenn der Zuordnungsalgorhytmus automatisch oder manuell ausgeführt wird, findet er die passendsten Verknüpfungen zwischen Hilfesuchenden und Helferinnen und legt zwischen diesen eine Beziehung vom Typ "Mutual Aid" bzw. "Nachbarschaftshilfe" an. Die Zuordnung basiert auf räumlicher Nähe sowie (falls verwendet) die Anzahl der übereinstimmenden Hilfskategorien und beherrschter Sprachen der beiden Personen. Falls Hilfskategorien und Sprachen im Formular verwendet werden, muss es für beides zumindest eine Übereinstimmung geben.

Die Beziehung bekommt zunächst den Status"Prüfung erforderlich" (benutzerdefiniertes Feld). Jedes Mal wenn der Algorhytmus ausgeführt wird können Beziehungen mit diesem Status mit besseren Zuordnunegn überschrieben werden (allerdings nicht Beziehungen mit anderen Status).

Hilfesuchende Personen, Helfende und ihre Zuordnungen können über CiviCRM's eingebaute Suchfunktionen oder über die von der Erweiterung voreingestellten Berichte gefunden werden. Nachdem die Zuordnung geprüft wurde (dies kann auch beinhalten zu den Personen bspw. telefonsichen Kontakt aufzunehmen) solltest Du den Status der Beziehung zu "bestätigt", "kommuniziert" oder "abgebrochen" ändern. Wenn eine aktive Hilfsbeziehung nicht mehr genutzt wird, so sollte die Beziehung das passende Enddatum bekommen und auf inaktiv gesetzt werden (beides sind CiviCRM Kernfunktionen).

Falls nötig können jederzeit Beziehungen und Personen manuell in CiviCRM angelegt werden.

Personen mit Postadressen müssen manuell geocodiert werden, falls die automatische Geokodierung nicht funktioniert - hierzu muss man lediglich die Adresse bearbeiten und die Koordinaten von Hand eintragen. Alternativ kann auch eine Helferbeziehung manuell angelegt werden.

Der Zuordnungsalgorhytmus wird basierend auf den Einstellungen automatisch ausgeführt und kann zudem händisch ausgelöst werden.  

## Anmerkungen und geplante Funktionen
Wenn Du der Meinung bist, dass zusätzliche Funktionen der Erweiterung hinzugefügt werden sollen, erstelle bitte ein detailiertes Ticket auf Github. Bitte berücksichtige dabei, dass wir derzeit versuchen die Erweiterung so allgemein nutzbar und simpel wie möglich zu halten und deshalb nur Vorschläge berücksichtigen, die von allgemeinem Interesse sind. Natürlich kannst Du gerne dieses Repository klonen und die Erweiterung für Deine Zwecke anpassen. Alternativ sprich uns einfach an für indididuelle Anpassungen.

### Individuelle Formulare nutzen
Wir nutzen CiviCRM Formulare um die Erweiterung so einfach nutzbar wie möglich zu gestalten. Falls Du eigene Formulare erstellen möchtest (bspw. im CMS Deiner Webseite), so kannst Du dies tun und die eingegebenen Formulare mit Hilfe der REST API von CiviCRM übermitteln. Alle Aktionen wie bspw. die Formübermittlung oder das Auslösen des Zuordnungsmechanismus sind auch über die API verfügbar.

### Automatisierte Kommunikation
Momentan müssen die angelegten Beziehungen, Statusänderungen und die Kommunikation mit den Personen manuell erfolgen. Um diesen Prozess zu automatisieren empfehlen wir die Nutzung der [CiviRules](https://github.com/Kajakaran/org.civicoop.civirules) Extension und / oder anderer CiviCRM FUnktionen und Erweiterungen.
