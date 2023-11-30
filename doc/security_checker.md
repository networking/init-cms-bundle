Security Checker Command
========================

The networking initcms bundle provides a command to check your composer.lock file for known security issues, as well
as your yarn.lock file, or package-lock.json file.

The command is run as follows:

```
php bin/console networking:initcms:security-checker
```

In order for the command to work, you need to have the [symfony-cli](https://github.com/symfony-cli/symfony-cli) installed.

There are limitations to the command, as it only checks the composer.lock file, and yarn.lock or package-lock.json file for
known security issues, it does not check the actual code of your project, so it is not a complete security check.


