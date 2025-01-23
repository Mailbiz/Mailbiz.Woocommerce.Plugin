<?php

namespace Mailbiz;

use PHPUnit\Framework\TestCase;
use phpmock\mockery\PHPMockery;
use Mockery;

require_once __DIR__ . '/../../../../src/tracker/mailbiz-tracker.php';
use Mailbiz\Tracker;
// require_once __DIR__ . '/../../../../src/tracker/mailbiz-cart-id.php';
// use Mailbiz\Cart_Id;

require_once __DIR__ . '/../../mocks/wc-mock.php';
use MailbizTest\WcMock;

require_once __DIR__ . '/../../mocks/wp-terms-mock.php';
use MailbizTest\WpTermsMock;

//#region [BEFORE EACH]
function beforeEach()
{
    // Mock Cart_Id class
    $cartIdMock = Mockery::mock('alias:Mailbiz\\Cart_Id');
    $cartIdMock->shouldReceive('get')->andReturn('TEST_CART_ID');

    // Mock get_woocommerce_currency method
    PHPMockery::mock('Mailbiz', 'get_woocommerce_currency')->andReturn('BRL');

    // Mock is_wp_error
    PHPMockery::mock('Mailbiz', 'is_wp_error')->andReturn(false);
}
//#endregion

pest()->extend(TestCase::class)
    ->beforeAll(function () {
        //
    })->beforeEach(function () {
        beforeEach();
    })->afterEach(function () {
        Mockery::close();
    })->afterAll(function () {
        //
    })->group('mailbiz-tracker');

//#region [GIVEN]
function givenAnEmptyCart()
{
    $wc = new WcMock();
    $wc->_setData(include __DIR__ . '/_wcCartEmpty.php');
    PHPMockery::mock('Mailbiz', 'WC')->andReturn($wc);
}

function givenASimpleCart()
{
    $wc = new WcMock();
    $wc->_setData(include __DIR__ . '/_wcCartSimple.php');
    PHPMockery::mock('Mailbiz', 'WC')->andReturn($wc);

    // $terms = new WpTermsMock();
    $productInternalId = reset($wc->_getData()['cart']['cart'])['key'];
    // $terms->addTerm($productInternalId, [
    //     'product_cat' => ['Videogames'],
    // ]);
    $termsMock = PHPMockery::mock('Mailbiz', 'get_the_terms')->matchArgs([$productInternalId, 'product_cat'])->andReturn(['Videogames']);
    // $termsMock->
    // ->(function ($id, $term) use ($terms) {
    //     return $terms->getTheTerms($id, $term);
    // });
}

function givenThatIsNotCartPage()
{
    PHPMockery::mock('Mailbiz', 'is_cart')->andReturn(false);
}

function givenThatIsCartPage()
{
    PHPMockery::mock('Mailbiz', 'is_cart')->andReturn(true);
}
//#endregion

//#region [WHEN]
function whenCallingGetCartSyncEvent(): array
{
    return Tracker::get_cart_sync_event();
}
//#endregion

//#region [THEN]
//#endregion

test('Tracker: should get empty cart.sync event', function () {
    givenAnEmptyCart();
    givenThatIsNotCartPage();

    $event = whenCallingGetCartSyncEvent();

    expect($event)->toBe(include __DIR__ . '/_cartSyncEventEmpty.php');
})->group('mailbiz-tracker');

test('Tracker: should get simple cart.sync event', function () {
    givenASimpleCart();
    givenThatIsNotCartPage();

    $event = whenCallingGetCartSyncEvent();

    expect($event)->toBe(include __DIR__ . '/_cartSyncEventSimple.php');
})->group('mailbiz-tracker');

test('Tracker: should not track when there\'s an order_id set', function () { })
    ->group('mailbiz-tracker')
    ->skip();

test('Tracker: should get complete cart.sync event', function () { })
    ->group('mailbiz-tracker')
    ->skip();
