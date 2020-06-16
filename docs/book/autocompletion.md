# Autocompletion

laminas-cli does not provide autocompletion out of the box. However, it is
possible to add autocompletion via a third-party package,
[bamarni/symfony-console-autocomplete](https://github.com/bamarni/symfony-console-autocomplete).
This package provides completion for any symfony/console-based application,
which means it will work with laminas-cli as well.

First, install the autocompletion package as a global tool:

```bash
composer global require bamarni/symfony-console-autocomplete
```

Second, you will need to add configuration for your shell. We will add three
things:

- Configuration to ensure your Composer script path is in your `$PATH`.
- An alias for the laminas-cli script. This is done so that autocompletion is
  given for the project in which you have it installed.
- The autocompletion script.

For most shells, you can use the following:

```bash
# Skip this step if you already have the Composer script path in your $PATH
export PATH=$(composer global config home)/vendor/bin:$PATH

# Alias laminas-cli to current path
alias laminas=./vendor/bin/laminas

# Add the autocompletion script, informing it to also complete laminas
eval "$(symfony-autocomplete --aliases laminas)"
```

The above should be placed in your shell configuration file:

- For BASH users, `$HOME/.bashrc`
- For ZSH users, `$HOME/.zshrc`
- For FISH users, `$HOME/.config/fish/config.fish`

Once the changes have been made, source your shell configuration:

- For BASH users, `source $HOME/.bashrc`
- For ZSH users, `source $HOME/.zshrc`
- For FISH users, `source $HOME/.config/fish/config.fish`

At this point, when you are in a project that uses laminas-cli, you can type:

```bash
$ laminas <TAB>
```

and you will see completion for the available commands.
