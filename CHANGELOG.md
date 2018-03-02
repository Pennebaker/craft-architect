# pennebaker/craft-architect Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.1] - 2018-03-02
## Added
- [Super Table] field import/export support.

## Changed
- Backups are now off by default.
- Moved import into a service to facilitate external usage.

## Fixed
- Add class scope to architect sidebar to prevent styling main CP nav styles. 

## [2.2.0] - 2018-02-18
## Added
- User Group Importing
- User Group Exporting
- User Importing
- User Exporting

## Changed
- Import page layout.

## [2.1.4] - 2018-02-17
## Added
- Copy to clipboard button on export results page.

## Fixed
- Javascript errors when not on export page.

## Changed
- Moved exported json into textarea

## [2.1.3] - 2018-02-17
## Fixed
- Errors if a string is passed into a `getById()` function.

## [2.1.2] - 2018-02-17
## Fixed
- User group errors when not using Craft Pro.

## [2.1.1] - 2018-02-16
## Fixed
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

[Unreleased]: https://github.com/pennebaker/craft-architect/compare/2.2.1...develop
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

[Super Table]: https://github.com/verbb/super-table