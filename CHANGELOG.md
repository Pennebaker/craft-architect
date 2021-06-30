# pennebaker/craft-architect Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.4.3] - 2021-06-30
### Fixed
- Fixed Neo export when using child blocks.
- Fixed processing of fields inside super table fields.

## [2.4.2] - 2021-03-19
### Fixed
- Fixed importing entry types into existing sections.
- Fixed field layout errors for Field Layout UI elements.

## [2.4.1] - 2021-01-29
### Fixed
- Fixed composer requirements to allow Craft 3.6

## [2.4.0] - 2021-01-04
### Added
- Craft 3.5 Field Layout Support
- Neo Field partial support for 3.5 Field Layout

### Fixed
- Fix entry type export error
- Fix Matrix field not updating.

## [2.3.3] - 2020-04-22
### Fixed
- Fix support for Type Link Field
- Fix Update support check

## [2.3.2] - 2020-01-31
### Fixed
- Fix getSiteById() error when exporting
- Fix lost data in SuperTables when updating a Matrix containing SuperTables
- Fix invalid argument supplied for foreach() when importing routes on a site with no routes.

## [2.3.1] - 2019-04-22
### Fixed
- Fix symfony/yaml version conflicts with CraftCMS

## [2.3.0] - 2019-03-04
### Added
- YAML Support
- Command Line Importing
- Build Order Importing
- Route Importing
- Route Exporting
- Import and Update Fields

## [2.2.12] - 2019-01-24
### Fixed
- Craft 3.1 fix for UIDs [source key references].
- Tag Group and Volume Field layout tabs don't have to be named "Content".

[source key references]: https://craftcms.com/guides/updating-plugins-for-craft-3-1#update-element-source-key-references

## [2.2.11] - 2018-11-14
### Added
- [Neo] field import/export support.

[Neo]: https://github.com/spicywebau/craft-neo

## [2.2.10] - 2018-09-11
### Fixed
- Fix section uriFormats.
- Fix importing a section without title labels/formats.
- Fix unknown sections throwing an error during export.
- Fix import of Super Tables containing Matrix fields.

## [2.2.9] - 2018-06-11
### Fixed
- Fix section source failure to map when chosen section is a single.

## [2.2.8] - 2018-04-06
### Fixed
- Fix folder source failure to map when set to use all sources.

## [2.2.7] - 2018-04-02
### Fixed
- Asset field source mapping on import/export.
- Global field layout not importing.

## [2.2.6] - 2018-03-22
### Fixed
- SuperTable import source mapping.
- SuperTable export source unmapping.
- SuperTable export when containing a Date field.

### Changed
- Roadmap: Pushed back migrations in favor of command line and ability to import and update existing structures.

## [2.2.5] - 2018-03-14
### Fixed
- Fix global set import failing on FieldLayout

## [2.2.4] - 2018-03-12
### Added
- [Typed Link] field import/export support.

### Fixed
- Fixed changelog formatting for CraftCMS to parse properly.

### Changed
- Proper error for source mapping failures during import.

[Typed Link]: https://github.com/sebastian-lenz/craft-linkfield

## [2.2.3] - 2018-03-06
### Fixed
- Fix matrix import processing.

## [2.2.2] - 2018-03-06
### Fixed
- Fixed processing of fields that use typesettings.

## [2.2.1] - 2018-03-02
### Added
- [Super Table] field import/export support.

### Changed
- Backups are now off by default.
- Moved import into a service to facilitate external usage.

### Fixed
- Add class scope to architect sidebar to prevent styling main CP nav styles.

[Super Table]: https://github.com/verbb/super-table

## [2.2.0] - 2018-02-18
### Added
- User Group Importing
- User Group Exporting
- User Importing
- User Exporting

### Changed
- Import page layout.

## [2.1.4] - 2018-02-17
### Added
- Copy to clipboard button on export results page.

### Fixed
- Javascript errors when not on export page.

### Changed
- Moved exported json into textarea

## [2.1.3] - 2018-02-17
### Fixed
- Errors if a string is passed into a `getById()` function.

## [2.1.2] - 2018-02-17
### Fixed
- User group errors when not using Craft Pro.

## [2.1.1] - 2018-02-16
### Fixed
- Removed export not available notification.

## [2.1.0] - 2018-02-16
### Added
- Redactor Field Importing
- Site Group Exporting
- Site Exporting
- Section Exporting
- Entry Type Exporting
- Volume Exporting
- Asset Transform Exporting
- Tag Group Exporting
- Category Group Exporting
- Field Exporting
- Global Set Exporting

## [2.0.0] - 2018-01-29
### Added
- Initial Release

### Fixed
- Strings not translatable

## [2.0.0-beta.3] - 2018-01-27
### Added
- Site Group Importing
- Site Importing
- Asset Volume Importing
- Asset Transform Importing
- Tag Group Importing
- Category Group Importing
- Global Set Importing
- Exception & Error catching during parse and save.

### Fixed
- Fixed error in imported Asset Fields causing issue with saving sections.

## [2.0.0-beta.2] - 2018-01-27
### Added
- Section Importing
- Entry Type Importing

## 2.0.0-beta.1 - 2018-01-26
### Added
- Field Group Importing
- Field Importing

[Unreleased]: https://github.com/pennebaker/craft-architect/compare/2.4.3...develop
[2.4.3]: https://github.com/pennebaker/craft-architect/compare/2.4.2...2.4.3
[2.4.2]: https://github.com/pennebaker/craft-architect/compare/2.4.1...2.4.2
[2.4.1]: https://github.com/pennebaker/craft-architect/compare/2.4.0...2.4.1
[2.4.0]: https://github.com/pennebaker/craft-architect/compare/2.3.3...2.4.0
[2.3.3]: https://github.com/pennebaker/craft-architect/compare/2.3.2...2.3.3
[2.3.2]: https://github.com/pennebaker/craft-architect/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/pennebaker/craft-architect/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/pennebaker/craft-architect/compare/2.2.12...2.3.0
[2.2.12]: https://github.com/pennebaker/craft-architect/compare/2.2.11...2.2.12
[2.2.11]: https://github.com/pennebaker/craft-architect/compare/2.2.10...2.2.11
[2.2.10]: https://github.com/pennebaker/craft-architect/compare/2.2.9...2.2.10
[2.2.9]: https://github.com/pennebaker/craft-architect/compare/2.2.8...2.2.9
[2.2.8]: https://github.com/pennebaker/craft-architect/compare/2.2.7...2.2.8
[2.2.7]: https://github.com/pennebaker/craft-architect/compare/2.2.6...2.2.7
[2.2.6]: https://github.com/pennebaker/craft-architect/compare/2.2.5...2.2.6
[2.2.5]: https://github.com/pennebaker/craft-architect/compare/2.2.4...2.2.5
[2.2.4]: https://github.com/pennebaker/craft-architect/compare/2.2.3...2.2.4
[2.2.3]: https://github.com/pennebaker/craft-architect/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/pennebaker/craft-architect/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/pennebaker/craft-architect/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/pennebaker/craft-architect/compare/2.1.4...2.2.0
[2.1.4]: https://github.com/pennebaker/craft-architect/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/pennebaker/craft-architect/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/pennebaker/craft-architect/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/pennebaker/craft-architect/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/pennebaker/craft-architect/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/pennebaker/craft-architect/compare/2.0.0-beta.3...2.0.0
[2.0.0-beta.3]: https://github.com/pennebaker/craft-architect/compare/2.0.0-beta.2...2.0.0-beta.3
[2.0.0-beta.2]: https://github.com/pennebaker/craft-architect/compare/2.0.0-beta.1...2.0.0-beta.2
