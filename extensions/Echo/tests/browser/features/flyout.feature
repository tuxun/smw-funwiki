@chrome @en.wikipedia.beta.wmflabs.org @firefox @login @test2.wikipedia.org
Feature: Flyout

  Background:
    Given I am on the "Selenium Echo flyout test page" page

  Scenario: Flyout button not present when anon
    Then I do not see the notification flyout button

  Scenario: Flyout button present
    Given I am logged in
    Then I see the notification flyout button

  Scenario: Flyout button present
    Given I am logged in
    When I click the notification flyout button
    Then I see the notification flyout
