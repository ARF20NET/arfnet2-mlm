# arfnet2-mlm
Lightweight and dead simple PHP mailing list manager for ARFNET using PHPMailer

No postfix aliases required

Design
```
FILES
    index.php
        Index of lists, links to subscribe.php
        Login link
    subscribe.php?list=<list>
        Subscription form to <list>
    admin.php
        List administration
        Link to publish
    managelists.php
        List admnistration
    managesubs.php
        Subscriber administration
    managearchive.php
        Archive administration
    publish.php
        Publish form

SQL
    lists
        id, name, type
    subscribers
        id, email, list id, unsub code, date, status { active, inactive }
    archive
        id, list, message, author, date

```
