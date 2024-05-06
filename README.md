# PHP Function of The Day

A static website built with PHP to showcase obscure and underrated built-in PHP functions

## About

The website is generated statically using PHP, with a JSON database assembled from a dataset created using code in the `src/` directory, interacting with data in the `docs/` folder.
The frontend is built with vanilla HTML, CSS, and JavaScript, with the static website files hosted on GitHub Pages, through the `docs/` directory.

### Attributions

The database generator uses dataset by [Exact.io](https://www.exakat.io/en/top-100-php-functions), of the 100 most popular functions 
found in almost 2000 open source PHP projects, in order to diff out the most popular functions and highlight the lesser known ones.

Additionally, extracts from the PHP Manual are used to provide descriptions of the functions, with the PHP Manual being licensed under the [Creative Commons Attribution 3.0 License](https://github.com/php/doc-base/blob/master/LICENSE).
This dataset is parsed through https://www.php.net/manual/en/indexes.functions.php to extract the function descriptions.
