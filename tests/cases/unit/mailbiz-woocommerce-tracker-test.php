<?php

use PHPUnit\Framework\TestCase;
use phpmock\mockery\PHPMockery;
// use Mockery;

pest()->extend(TestCase::class)->afterEach(function () {
    Mockery::close();
})->group('plugin-starting-point');

//#region [GIVEN]
function givenAbspathDefined()
{
    if (!defined("ABSPATH")) {
        define("ABSPATH", '/');
    }
}

function givenCliAccess()
{
    define("WP_CLI", true);
    PHPMockery::mock("Mailbiz", "is_admin")->andReturn(false);
}

function givenAdminAccess()
{
    runkit7_constant_redefine('WP_CLI', false);
    PHPMockery::mock("Mailbiz", "is_admin")->andReturn(true);
}

function givenClientAccess()
{
    runkit7_constant_redefine('WP_CLI', false);
    PHPMockery::mock("Mailbiz", "is_admin")->andReturn(false);
}

function givenAddActionMock()
{
    return PHPMockery::mock("Mailbiz", "add_action")->andReturn(null);
}
//#endregion

//#region [WHEN]
function whenLoadingPlugin()
{
    PHPMockery::mock("Mailbiz", "plugins_url")->andReturn('');
    require __DIR__ . '/../../../src/mailbiz-woocommerce-tracker.php';
}
//#endregion

//#region [THEN]
function pass()
{
    // Pest complains if it has no tests. Tests made by mockery aren't seen.
    expect(true)->toBeTrue();
}
//#endregion


test('Plugin starting point: should return if WP_CLI', function () {
    givenAbspathDefined();
    givenCliAccess();
    $addAction = givenAddActionMock();
    $addAction->never()->withAnyArgs();

    whenLoadingPlugin();

    expect(defined('MAILBIZ_PLUGIN_DIR'))->toBeFalse();
    expect(defined('MAILBIZ_PLUGIN_URL'))->toBeFalse();
})->group('plugin-starting-point');

test('Plugin starting point: should define MAILBIZ_PLUGIN_DIR and MAILBIZ_PLUGIN_URL', function () {
    givenAbspathDefined();
    givenAdminAccess();
    givenAddActionMock();

    whenLoadingPlugin();

    expect(MAILBIZ_PLUGIN_DIR)->toBeString();
    expect(MAILBIZ_PLUGIN_URL)->toBeString();
})->group('plugin-starting-point');

test('Plugin starting point: should add mailbiz-admin', function () {
    givenAbspathDefined();
    givenAdminAccess();
    $addAction = givenAddActionMock();
    $addAction->once()->withArgs(['init', ['Mailbiz\\Admin', 'init']]);

    whenLoadingPlugin();

    $addAction->verify();
    pass();
})->group('plugin-starting-point');

test('Plugin starting point: should add mailbiz-recovery and mailbiz-client', function () {
    givenAbspathDefined();
    givenClientAccess();

    $classesAdded = [
        'Mailbiz\\Recovery' => false,
        'Mailbiz\\Client' => false
    ];

    $addAction = givenAddActionMock();
    $addAction->twice()->withArgs(function ($firstArg, $secondArg) use (&$classesAdded) {
        $firstCorrect = $firstArg === 'init';
        $secondPartTwoCorrect = $secondArg[1] === 'init';
        $secondPartOneCorrect = $secondArg[0] === 'Mailbiz\\Recovery' || $secondArg[0] === 'Mailbiz\\Client';
        if ($secondPartOneCorrect) {
            $classesAdded[$secondArg[0]] = true;
        }
        return $firstCorrect && $secondPartTwoCorrect && $secondPartOneCorrect;
    });

    whenLoadingPlugin();

    $addAction->verify();
    expect($classesAdded['Mailbiz\\Recovery'])->toBeTrue();
    expect($classesAdded['Mailbiz\\Client'])->toBeTrue();
})->group('plugin-starting-point');
