# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.1.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2020-06-16

### Added

- Adds a vendor binary, `laminas`, for use in both Laminas MVC and Mezzio applications, and which can be extended via configuration in applications and packages. See the [integration documentation](https://docs.laminas.dev/laminas-cli/intro/#integrating-in-components) for details.  

- Provides the ability for users to supply their own [PSR-11 container](https://www.php-fig.org/psr/psr-11/) for supplying configuration and commands to the `laminas` binary; see the [integration documentation](https://docs.laminas.dev/laminas-cli/intro/#integration-in-other-applications) for more details.

- Provides the ability to chain multiple commands; see the [command chains documentation](https://docs.laminas.dev/laminas-cli/command-chains/) for more information.

- Provides the ability to define input "parameters"; these act like input options with the additional behavior that, in interactive mode, if the value is not supplied, the application prompts the user interactively for the value.  Parameters may accept multiple values, either via repeated option invocations, or by prompting. See the [command params documentation](https://docs.laminas.dev/laminas-cli/command-params/) for more information.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
