<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ApiClientInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;
use Webmozart\Assert\Assert;

final class ManagingShippingCategoriesContext implements Context
{
    /** @var ApiClientInterface */
    private $client;

    public function __construct(ApiClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Given I want to create a new shipping category
     */
    public function iWantToCreateANewShippingCategory(): void
    {
        $this->client->buildCreateRequest('shipping_categories');
    }

    /**
     * @Given I want to modify a shipping category :shippingCategory
     */
    public function iWantToModifyAShippingCategory(ShippingCategoryInterface $shippingCategory): void
    {
        $this->client->buildUpdateRequest('shipping_categories', $shippingCategory->getCode());
    }

    /**
     * @When I add it
     * @When I try to add it
     */
    public function iAddIt(): void
    {
        $this->client->create();
    }

    /**
     * @When I delete shipping category :shippingCategory
     */
    public function iDeleteShippingCategory(ShippingCategoryInterface $shippingCategory): void
    {
        $this->client->delete('shipping_categories', $shippingCategory->getCode());
    }

    /**
     * @When I browse shipping categories
     */
    public function iBrowseShippingCategories(): void
    {
        $this->client->index('shipping_categories');
    }

    /**
     * @When I do not specify its code
     * @When I specify its code as :code
     */
    public function iSpecifyItsCodeAs(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->addRequestData('code', $code);
        }
    }

    /**
     * @When I name it :name
     * @When I do not specify its name
     * @When I rename it to :name
     */
    public function iNameIt(?string $name = null): void
    {
        if ($name !== null) {
            $this->client->addRequestData('name', $name);
        }
    }

    /**
     * @When I modify a shipping category :shippingCategory
     */
    public function iModifyAShippingCategory(ShippingCategoryInterface $shippingCategory): void
    {
        $this->client->buildUpdateRequest('shipping_categories', $shippingCategory->getCode());
    }

    /**
     * @When I specify its description as :description
     */
    public function iSpecifyItsDescriptionAs(string $description): void
    {
        $this->client->addRequestData('description', $description);
    }

    /**
     * @When I save my changes
     */
    public function iSaveMyChanges(): void
    {
        $this->client->update();
    }

    /**
     * @Then I should be notified that shipping category with this code already exists
     */
    public function iShouldBeNotifiedThatShippingCategoryWithThisCodeAlreadyExists(): void
    {
        Assert::same($this->client->getError(), 'code: The shipping category with given code already exists.');
    }

    /**
     * @Then I should be notified that :element is required
     */
    public function iShouldBeNotifiedThatElementIsRequired(string $element): void
    {
        Assert::same(
            $this->client->getError(), sprintf('%s: Please enter shipping category %s.', $element, $element)
        );
    }

    /**
     * @Then I should see a single shipping category in the list
     * @Then I should see :count shipping categories in the list
     */
    public function iShouldSeeShippingCategoriesInTheList(int $count = 1): void
    {
        $this->client->index('shipping_categories');
        Assert::same($this->client->countCollectionItems(), $count);
    }

    /**
     * @Then /^the (shipping category "([^"]+)") should be in the registry$/
     * @Then /^the (shipping category "([^"]+)") should appear in the registry$/
     */
    public function theShippingCategoryShouldAppearInTheRegistry(ShippingCategoryInterface $shippingCategory): void
    {
        $shippingCategoryName = $shippingCategory->getName();
        Assert::true(
            $this->isItemOnIndex('name', $shippingCategoryName),
            sprintf('Shipping category with name %s does not exists', $shippingCategoryName)
        );
    }

    /**
     * @Then shipping category with name :name should not be added
     */
    public function shippingCategoryWithNameShouldNotBeAdded(string $name): void
    {
        Assert::false(
            $this->isItemOnIndex('name', $name),
            sprintf('Shipping category with name %s exists', $name)
        );
    }

    /**
     * @Then /^(this shipping category) should no longer exist in the registry$/
     */
    public function thisShippingCategoryShouldNoLongerExistInTheRegistry(
        ShippingCategoryInterface $shippingCategory
    ): void {
        $shippingCategoryName = $shippingCategory->getName();
        Assert::false(
            $this->isItemOnIndex('name', $shippingCategoryName),
            sprintf('Shipping category with name %s exist', $shippingCategoryName)
        );
    }

    /**
     * @Then I should not be able to edit its code
     */
    public function iShouldNotBeAbleToEditItsCode(): void
    {
        $this->client->addRequestData('code', 'NEW_CODE');
        $this->client->update();

        Assert::false($this->client->hasValue('code', 'NEW_CODE'), 'The code field with value NEW_CODE exists');
    }

    /**
     * @Then there should still be only one shipping category with code :code
     */
    public function thereShouldStillBeOnlyOneShippingCategoryWith(string $code): void
    {
        $this->client->index('shipping_categories');
        Assert::same(count($this->client->getCollectionItemsWithValue('code', $code)), 1);
    }

    /**
     * @Then this shipping category name should be :name
     */
    public function thisShippingCategoryNameShouldBe(string $name): void
    {
        Assert::true(
            $this->client->hasValue('name', $name),
            sprintf('Shipping category with name %s does not exists', $name)
        );
    }

    private function isItemOnIndex(string $property, string $value): bool
    {
        $this->client->index('shipping_categories');
        return $this->client->hasItemWithValue($property, $value);
    }
}
