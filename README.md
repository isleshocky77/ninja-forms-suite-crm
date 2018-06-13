# About #

This is _ninja-forms-suite-crm_. A [Wordpress] plugin and [Ninja Forms][ninja-forms] add-on which 
allows you to save form data to [Suite Crm][suite-crm]. 

*Note* This was developed, tested, and currently for use with [Suite Crm][suite-crm] 7.10 whose API
v8.

## Configuration ##

1. Create OAuth Credentials on Suite CRM from Admin > OAuth Keys
2. Install and activate _ninja-forms-suite-crm_
3. Navigate to Forms > Settings
4. Under **Suite Crm Settings** fill in authentication information and click "Save"
5. Click Generate Code
6. Authorize Code on Suite Crm
7. Fill in "Code" and click "Save"
8. Click "Generate Access Token"
9. Click "Refresh Objects"

## Usage ##

* Navigate to editing a form
* Go to **Emails & Actions** and add "Send to Suite"
* Configure the action by adding _Field Mappings_
  * The left side should be the _admin key_ for the ninja form
  * The right side should be the API field name of Suite (e.g. firstname, lastname, email, phone, company)

## License ##

    ninja-forms-suite-crm is licensed under GPLv3.

    ninja-forms-suite-crm is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ninja-forms-suite-crm is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ninja-forms-suite-crm.  If not, see <http://www.gnu.org/licenses/>.

# Changelog #

## 3.0 ##

Release: 2018-06-12

First Version
Changes ninja-forms-sugar-crm to ninja-forms-suite-crm to work with API v8

[suite-crm]: https://suitecrm.com/
[ninja-forms]: https://ninjaforms.com/
[wordpress]: https://wordpress.com/
[suite-crm-6-5-api-docs]: http://support.suitecrm.com/Documentation/Suite_Developer/Suite_Developer_Guide_6.5/Application_Framework/Web_Services/REST/
