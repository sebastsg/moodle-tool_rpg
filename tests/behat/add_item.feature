@tool @tool_rpg
Feature: Add a new item to the RPG.
  In order to create a fun RPG experience
  As a teacher
  I need to be able to add items to the RPG.

  @javascript @_file_upload
  Scenario: Teacher can add a new item in edit mode.
    Given I log in as "admin"
    And I turn editing mode on
    And I select "RPG" from primary navigation
    Then I click create new item button
    And I should see "Name"
    And I should see "Rarity"
    And I should see "Type"
    And I should see "Icon"
    Then I set the following fields to these values:
      | name      | Potion  |
      | rarity    | common  |
      | type      | potion  |
      | stackable | 1       |
    And I upload "admin/tool/rpg/tests/fixtures/item.png" file to "Icon" filemanager
    And I press "Save changes"
    And I wait to be redirected
    Then the following should exist in the "tool_rpg-item-list" table:
      | Name   | Rarity | Type   |
      | Potion | Common | Potion |
