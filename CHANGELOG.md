# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.1.7 - TBD

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

## 0.1.6 - 2021-01-21

### Added

- [#54](https://github.com/laminas/laminas-cli/pull/54) adds the ability to define a service `Laminas\Cli\SymfonyEventDispatcher` that returns a `Symfony\Component\EventDispatcher\EventDispatcherInterface` implementation for use with the symfony/console application exposed by laminas-cli. When present, that instance will be used, and the internal `TerminateListener` attached to it. Otherwise, a `Symfony\Component\EventDispatcher\EventDispatcher` will be created internally (previous behavior). See the [events documentation](https://docs.laminas.dev/laminas-cli/events/) for more details.

### Changed

- [#58](https://github.com/laminas/laminas-cli/pull/58) changes when parameter normalization occurs when a parameter is passed as an argument or option. Previously, it was validating first, then normalizing; however, this is the opposite order to how those operations are performed when asked via a prompt. This release updates to use the same order as prompting (normalization then validation); in most cases, this should lead to resolution of false negative validations.

- [#53](https://github.com/laminas/laminas-cli/pull/53) changes the behavior of `ParamInputInterface` implementations with regards to reporting third-party commands. Previously, any command not shipped via Laminas or Mezzio was flagged as a third-party command; now, commands with namespaces that do not originate in the Composer vendor directory will not be flagged as third-party commands (with the assumption that these have been developed in the target application, and are thus local).


-----

### Release Notes for [0.1.6](https://github.com/laminas/laminas-cli/milestone/5)

0.1.x bugfix release (patch)

### 0.1.6

- Total issues resolved: **4**
- Total pull requests resolved: **6**
- Total contributors: **4**

#### Enhancement

 - [61: Replace $HOME and ~ at start of vendor-dir setting](https://github.com/laminas/laminas-cli/pull/61) thanks to @weierophinney and @michalbundyra
 - [57: Ensure TerminateListener works correctly for null return values from commands](https://github.com/laminas/laminas-cli/pull/57) thanks to @weierophinney
 - [56: Perform QA improvements for PHPUnit and Psalm](https://github.com/laminas/laminas-cli/pull/56) thanks to @weierophinney
 - [54: Allow defining an EventDispatcher service in the container](https://github.com/laminas/laminas-cli/pull/54) thanks to @weierophinney and @vaclavvanik
 - [53: Remove third-party notice for local commands](https://github.com/laminas/laminas-cli/pull/53) thanks to @weierophinney and @michalbundyra

#### Bug

 - [58: Ensure parameter normalization occurs before validation when parameter is passed as an option during invocation](https://github.com/laminas/laminas-cli/pull/58) thanks to @weierophinney and @jbh

## 0.1.5 - 2020-10-24

### Added

- [#50](https://github.com/laminas/laminas-cli/pull/50) Add PHP 8.0 support


-----

### Release Notes for [0.1.5](https://github.com/laminas/laminas-cli/milestone/4)



### 0.1.5

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement,hacktoberfest-accepted

 - [50: PHP 8.0 support](https://github.com/laminas/laminas-cli/pull/50) thanks to @Thaix

## 0.1.4 - 2020-08-10

### Added

- Nothing.

### Changed

- This version introduces static analysis tools into the development process, as well as usage of webmozart/assert for type assertions. The primary impact is on developers of new commands, as we will now be throwing `InvalidArgumentException` where before we were throwing either `RuntimeException` or `Laminas\Cli\Exception\ConfigurationException`, for invalid user input.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.3 - 2020-07-30

### Added

- Nothing.

### Changed

- [#37](https://github.com/laminas/laminas-cli/pull/37) modifies how chained commands work. When a command within a chain is executed, if it is not one provided by the Laminas Project (or its subprojects), a warning is emitted when prompting to execute it.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.2 - 2020-07-29

### Added

- Nothing.

### Changed

- [#36](https://github.com/laminas/laminas-cli/pull/36) changes the dependency from `ocramius/package-versions` to `composer/package-versions-deprecated` to ensure compatibility with Composer v2 on PHP 7.3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2020-06-30

### Added

- Nothing.

### Changed

- [#34](https://github.com/laminas/laminas-cli/pull/34) makes it possible to omit adding dependency configuration for commands that can be instantiated without arguments.

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
