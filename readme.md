# Laravel Envoy

Elegant SSH tasks for PHP.

This Fork of the Laravel Envoy repository adds functionality that allows you to supply extra SSH parameters along with your envoy command. This gives you the ability to supply parameters like login name and identity file to the remote connection.

For example:
```
$ envoy run {task_name} --ssh_params="-i ~/.ssh/custom-key.pub -l non_default_user"
```

This option is not required.


Official documentation [is located here](http://laravel.com/docs/envoy).
