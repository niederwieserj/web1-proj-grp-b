# ToDo List 

* Redesign readme.md for better readability
* Update issue templates
* im admin bereich tags verwaltung
* wenn man sich mit falschen anmlededaten anmeldet kommt man zu login failed seite
* bei all articles soll man möglichkeit haben zu suchen und view articles
* in editor summary weggeben
* wenn man 2 selbe accounts erstellt kommt db fehler
* in all_articles zusätzlich user wer verfasst hat

Ergänzen:
* edit_article
* delete_article
* all_articles --> Buttons zum edit article und delete article
* my_articles --> Buttons zum edit article und delete article
* admin_panel

BUG:
DB user table hat spalte is_active und bei admin panel beim user löschen wird user nicht gelöscht, sondern set_active auf 0 gesetzt
ebenso dazu wird bei articles set_active auf 0 gesetzt --> ABER artikel zu user werden nicht auf is_active 0 gesetzt, weil in db user_id aus users und fk_user_id aus artikles wsl nicht übereinstimmen