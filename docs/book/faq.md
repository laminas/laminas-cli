# FAQ

## Error when installing due to ocramius/package-versions

Do you receive the following error when attempting to install
laminas/laminas-cli in your application?

```text
don't install ocramius/package-versions 1.9.0|don't install composer/package-versions-deprecated 1.10.99
```

If so, you will also need to require `composer/package-versions-deprecated` when
installing `laminas/laminas-cli`:

```bash
$ composer require composer/package-versions-deprecated laminas/laminas-cli
```

[composer/package-versions-deprecated](https://github.com/composer/package-versions-deprecated)
is a drop-in replacement for ocramius/package-versions that works across a
broader number of PHP versions. It is marked "deprecated" as Composer v2 will
incorporate the functionality it provides natively.
