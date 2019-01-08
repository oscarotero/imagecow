# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - Unreleased

### Added

- This CHANGELOG.md
- Better typings

### Changed

- All protected methods/properties are now private
- All classes are final
- Renamed internal classes (Libs -> Adapters)
- Renamed `Image::setBackground()` to `Image::background()`
- Renamed `Image::setClientHints()` to `Image::clientHints()`

### Removed

- Support for php < 7.2
- IconExtractor utility
- SvgExtractor utility
- Removed autoloader.php, use Composer or any other psr-4 loader
- Removed `top`, `right`, `bottom` and `left` keywords. Use percentages instead.