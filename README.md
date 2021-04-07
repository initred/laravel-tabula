# laravel-tabula
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->
laravel-tabula is a tool for liberating data tables trapped inside PDF files for the Laravel framework. This package was inspired by Python’s tabula-py package.

### How to install


```
composer require initred/laravel-tabula
```

### Configuration Settings (Needed Java)

[Windows]

http://www.oracle.com/technetwork/java/javase/downloads/index.html.
Please System Path Adding.

[Mac os]

```
brew update
```
```
brew cask install java
```

[Debian]

```
sudo apt install default-jre
```

[Fedora]

```
sudo dnf install java-latest-openjdk
```

### How to use on Laravel (Example)

```
$file = storage_path('app/public/pdf/test.pdf')

$tabula = new Tabula('/usr/bin/');

$tabula->setPdf($file)
    ->setOptions([
        'format' => 'csv',
        'pages' => 'all',
        'lattice' => true,
        'stream' => true,
        'outfile' => storage_path("app/public/csv/test.csv"),
    ])
    ->convert();
```

### License

laravel-tabula is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://medium.com/@mantey"><img src="https://avatars.githubusercontent.com/u/39991756?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Daniel Mantey</b></sub></a><br /><a href="https://github.com/initred/laravel-tabula/commits?author=mantey-github" title="Code">💻</a> <a href="https://github.com/initred/laravel-tabula/commits?author=mantey-github" title="Documentation">📖</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!