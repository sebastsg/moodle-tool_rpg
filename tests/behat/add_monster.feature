@tool @tool_rpg
Feature: Add a new monster to the RPG.
  In order to create a fun RPG experience
  As a teacher
  I need to be able to add monsters to the RPG.

  @javascript @_file_upload
  Scenario: Teacher can add a new monster in edit mode.
    Given I log in as "admin"
    And I turn editing mode on
    And I select "RPG" from primary navigation
    Then I follow "Create new monster"
    And I should see "Name"
    And I should see "Hitpoints"
    And I should see "Level"
    And I should see "Icon"
    Then I set the following fields to these values:
      | name  | Imp |
      | hp    | 70  |
      | level | 5   |
    And I upload "admin/tool/rpg/tests/fixtures/monster.png" file to "Icon" filemanager
    And I press "Save changes"
    And I wait to be redirected
    Then the following should exist in the "tool_rpg-monster-list" table:
      | Name | Hitpoints | Level |
      | Imp  | 70        | 5     |
