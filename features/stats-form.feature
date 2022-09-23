Feature: Stats form usage
  As Supermetrics user
  I should be able select months and see statistics

  Scenario: Use default form settings
    Given I am on "/"
    When I press "Show statistics"
    And I wait 10 seconds
    Then I should see "Average number of posts per user in a given month"
    
  Scenario: Select April 2022
    Given I am on "/"
    When I select "April, 2022" from "month"
    And I press "Show statistics"
    And I wait 10 seconds
    Then I should see "Average number of posts per user in a given month"
  
  Scenario Outline: Select specific months
    Given I am on "/"
    When I select <month> from "month"
    And I press "Show statistics"
    And I wait 5 seconds
    Then I should see "Average number of posts per user in a given month"

    Examples:
    | month             | 
    | "April, 2022"     |
    | "May, 2022"       |
    | "June, 2022"      |
    | "July, 2022"      |
    | "August, 2022"    |
    | "September, 2022" |
