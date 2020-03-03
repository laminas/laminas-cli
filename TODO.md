# TODO

- [ ] Seamless integration with MVC and Mezzio
  - with Mezzio it should be easy as we have config/container.php already

- [ ] How we should provide configuration?
  My first suggestion was:
  
  ```php
  return [
      'cli' => [
          MyCommand::class,
      ],
  ];
  ```
  
  but we would need to have some distinction for prod/dev commands.
  Unless this would just depend on the package - if package is installed all
  commands provided in the component will be available.  

- [ ] Lazy loading command, so we do not need initialise all commands on run.

- [ ] Should we wrap also Symfony console application so our bin/laminas-cli is simpler?
