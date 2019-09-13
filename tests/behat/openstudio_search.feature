@ou @ou_vle @local @local_ousearch @javascript
Feature: Search content
    In order to search content
    As a student
    I need to be able to search within OpenStudio

    Background: Setup course and studio
        Given the following "users" exist:
          | username | firstname | lastname | email            |
          | student1 | Student   | 1        | student1@asd.com |
          | student2 | Student   | 2        | student2@asd.com |
        And the following "courses" exist:
          | fullname | shortname | category |
          | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
          | user     | course | role    |
          | student1 | C1     | student |
          | student2 | C1     | student |

        # Prepare a open studio
        And the following open studio "instances" exist:
          | course | name           | description                | pinboard | idnumber | tutorroles |
          | C1     | Sharing Studio | Sharing Studio description | 99       | OS1      | manager    |

        # Prepare open studio' contents and activities
        And the following open studio "contents" exist:
          | openstudio | user     | name              | description           | visibility |
          | OS1        | student1 | Student content 1 | Content Description 1 | private    |
          | OS1        | student1 | Student content 2 | Content Description 2 | private    |
          | OS1        | student1 | Student content 3 | Content Description 3 | module     |
          | OS1        | student1 | Student slot    4 | Slot Description 4    | module     |
        And the following open studio "level1s" exist:
          | openstudio | name | sortorder |
          | OS1        | B1   | 1         |
        And the following open studio "level2s" exist:
          | level1 | name | sortorder |
          | B1     | A1   | 1         |
        And the following open studio "level3s" exist:
          | level2 | name | sortorder |
          | A1     | S1   | 1         |
        And the following open studio "folder templates" exist:
          | level3 | additionalcontents |
          | S1     | 2                  |
        And the following open studio "folder content templates" exist:
          | level3 | name            |
          | S1     | folder_template |
        Given the following open studio "level3contents" exist:
          | openstudio | user     | name              | description           | weblink                                     | visibility | level3 | levelcontainer |
          | OS1        | student1 | Student content 5 | Content Description 5 | https://www.youtube.com/watch?v=ktAnpf_nu5c | module     | S1     | module         |
        # Use Legacy system for default.
        And the following config values are set as admin:
          | modulesitesearch | 2 | local_moodleglobalsearch |
          | activitysearch   | 1 | local_moodleglobalsearch |
        And Open Studio levels are configured for "Sharing Studio"
        And all users have accepted the plagarism statement for "OS1" openstudio

    Scenario: Search my content
        Given I log in as "student1"

        # Search my pinboard
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "My Content > My Pinboard" in the openstudio navigation
        And I set the field "Search My Pinboard" to "content"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "Student content 1"
        Then I should see "Student content 2"
        Then I should see "Student content 3"
        Then I should not see "Student slot 4"
        Then I should see "content — 3 results found"

        # Search my activity
        And I follow "My Content > My Activities" in the openstudio navigation
        And I set the field "Search My Activities" to "content"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "Student content 5"
        Then I should not see "Student content 1"
        Then I should not see "Student content 2"
        Then I should not see "Student content 3"
        Then I should not see "Student slot 4"
        Then I should see "content — 1 results found"

        # Search my module
        And I follow "Shared Content"
        And I set the field "Search My Module" to "content"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "Student content 1"
        Then I should see "Student content 2"
        Then I should see "Student content 3"
        Then I should see "Student content 5"
        Then I should not see "Student slot 4"
        Then I should see "content — 4 results found"
        # Search my module - pagination.
        Given the following config values are set as admin:
          | streampagesize | 2 | openstudio |
        When I follow "Shared Content"
        And I set the field "Search My Module" to "content"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "Student content 1"
        And I should see "Student content 2"
        And I should not see "Student content 3"
        And I should see "content — 2 or more results found"
        When I follow "More search results"
        Then I should see "Student content 3"
        And I should see "content — 2 or more results found"
        And the following config values are set as admin:
          | streampagesize | 100 | openstudio |

        # Search my module by another student
        Given I log out
        When I log in as "student2"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I set the field "Search My Module" to "content"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "Student content 3"
        Then I should see "Student content 5"
        Then I should not see "Student content 1"
        Then I should not see "Student content 2"
        Then I should not see "Student slot 4"
        Then I should see "content — 2 results found"

    Scenario: Search my folder
        Given I log in as "student1"

        Given the following open studio "folders" exist:
          | openstudio | user     | name                         | description                       | visibility | contenttype    |
          | OS1        | student1 | Student content folder 1     | My Folder Overview Description 1  | private    | folder_content |
          | OS1        | student1 | Student content folder 2     | My Folder Overview Description 2  | module     | folder_content |

        # Search folder in my pinboard view
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "My Content > My Pinboard" in the openstudio navigation
        And I set the field "Search My Pinboard" to "folder"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        And I should see "folder — 2 results found"
        And I should see "Student content folder 1"
        And I should see "Student content folder 2"

        # Search folder in my module by another student
        And I log out
        And I log in as "student2"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I set the field "Search My Module" to "folder"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        And I should see "folder — 1 results found"
        And I should not see "Student content folder 1"
        And I should see "Student content folder 2"

    Scenario: Search on a term that is not in the index
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I set the field "Search My Module" to "notthere"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "No results"

    Scenario: Search for a comment
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "Student content 3"
        And I press "Add new comment"
        And I set the field "Comment" to "Comment text"
        And I press "Post comment"
        And I follow "Shared Content"
        And I set the field "Search My Module" to "comment"
        When I submit the openstudio search form "#openstudio_searchquery" "css_element"
        Then I should see "comment — 1 results found"

    Scenario: Search for a tag
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "Student content 3"
        And I press "Edit"
        And I press "Add file"
        And I set the field with xpath "//input[@placeholder='Enter tags...']" to "tag1"
        And I press "Save"
        When I follow "tag1"
        Then I should see "tag:tag1 — 1 results found"
        And I log out

        # Enable folders
        And I log in as "admin"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "Administration > Edit" in the openstudio navigation
        And I follow "Expand all"
        And I set the field "Enable Folders" to "1"
        And I press "Save and display"
        And I should see "Create new folder"
        And I log out

        And I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Sharing Studio"
        And I follow "Create new folder"
        And I set the following fields to these values:
          | Who can view this folder  | My module                                  |
          | Folder title              | Test my folder view 1                      |
          | Folder description        | My folder view description 1               |
        And I press "Create folder"
        And I follow "Add new content"
        And I press "Add file"
        And I set the following fields to these values:
          | Title                     | Test My Group Board View 2                 |
          | Description               | My Group Board View Description 2          |
          | Files                     | mod/openstudio/tests/importfiles/test1.jpg |
          | Tags                      | tag2                                       |
        And I press "Save"
        When I follow "tag2"
        Then I should see "tag:tag2 — 1 results found"