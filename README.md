# PHP Function of The Day

A static website built with PHP to showcase obscure and underrated built-in PHP functions

## About

The website is generated statically using PHP, with a JSON database assembled from a dataset created using code in the `src` directory.
The website static website files are hosted on GitHub Pages, with the frontend built with vanilla HTML, CSS, and JavaScript.

### Attributions

The database generator uses dataset of the 100 most popular functions in almost 2000 open source PHP projects,
in order to diff out the most popular functions and highlight the lesser known ones. This dataset is made
by [Exact.io](https://www.exakat.io/en/top-100-php-functions/) and downloaded to the `data` directory.
