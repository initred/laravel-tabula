# laravel-tabula
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
