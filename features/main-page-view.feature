Feature: Main page view
  As Supermetrics potential employee
  I should be able to reach assignment application main page

  Scenario: Open application main page and find title text
    Given I am on "/"
    Then I should see "Fictional social platform"
    And I should see "posts statistics"
