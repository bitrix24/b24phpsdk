# A quick guide to contribute to the project:

## Installing the dev environment

1.  Fork the repo
2.  Clone the repo to local
3.  Install dependencies: `composer update` (this assumes you have 'composer' aliased to wherever your composer.phar lives)
4.  Run the tests. We only take pull requests with passing tests, and it's great to know that you have a clean slate.

## Adding new features

Pull requests with new features needs to be created against master branch. 

If new feature require BC Break please note that in your PR comment, it will added in next major version.
New features that does not have any BC Breaks are going to be added in next minor version.

## Codding standards

In order to fix codding standards please execute: 

```shell
make lint-phpstan
make lint-rector
make lint-rector-fix
```

## Code coverage

Unit tests collect code coverage with [pcov](https://github.com/krakjoe/pcov), it is installed in the `php-cli` docker container:

```shell
make test-unit-coverage
```

The command prints a summary of the code coverage metrics:

- **Lines** – line coverage, whether each executable line was executed
- **Methods** – function and method coverage, a method is covered only when all of its executable lines are covered
- **Classes** – class and trait coverage, a class or trait is covered only when all of its methods are covered

Reports are generated in the `var/coverage` folder: `html/index.html` – HTML report, `clover.xml` – Clover XML report.
In CI the summary is published in the job summary of the «Code coverage» job, the full report is available as the `code-coverage-report` artifact.

Most of the `src/Services` code is tested by integration tests, they are not executed in CI because they require a test Bitrix24 portal.
To get the full code coverage, configure `tests/.env.local` (see [Integration Tests Setup](CLAUDE.md)) and run:

```shell
make test-integration-coverage
```

The command runs unit tests and every integration scope test suite with `--coverage-php`, saves coverage of each suite in
`var/coverage/php/*.cov` and merges them with [phpcov](https://github.com/sebastianbergmann/phpcov) into `var/coverage/merged`
(`coverage.txt`, `html/index.html`, `clover.xml`). Failed test suites do not stop the run, they are listed at the end and fail the command.
The full run takes a long time and creates and deletes a lot of entities on the portal, you can run selected suites only:

```shell
make test-integration-coverage INTEGRATION_COVERAGE_SUITES="integration_tests_core integration_tests_scope_crm"
```

Code coverage counts only code declared in `#[CoversClass]` / `#[CoversMethod]` attributes of a test.

## Patches and bugfixes 

1. Check the oldest version that patch/bug fix can be applied.
2. Create PR against that version 


## The actual contribution

1.  Make the changes/additions to the code, committing often and making clear what you've done
2.  Make sure you write tests for your code, located in the folder structure 
3.  Run your tests (often and while coding)
4.  Create Pull Request on GitHub to against proper branch
