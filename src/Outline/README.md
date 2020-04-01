# Outline format

The format is to display pages in a hierarchical outline form, using one or more of the pages' properties as outline headers.

## Usage

```
{{#ask: [[Category:Task]]
 |?Severity
 |format=outline
 |outlineproperties=Severity, Assigned to
 |template=phab-view
 |introtemplate=intro
 |outrotemplate=outro
}}
```

For example, with `phab-view` as template name, the `outline` format will generate two distinct templates `phab-view-header` and `phab-view-item` to be used for the output with `-header` and `-item` being a fixed affix to distinguish the output level of the result content.

The `...-header` template will provide additional information and includes:
- `#outlinelevel` the level of the header being processed (depends on the iteration level invoked by the `outlineproperties` parameter)
- `#itemcount` provides a count information for the items assigned to the
the level
- `#userparam`

The `...-item` template will also provide additional information such as:

- `#itemsection` section number of the item
- `#itemsubject` the subject (aka page) of the item processed
- `#userparam` content provided by a user via the `#ask` `userparam` parameter

Use `introtemplate` \ `outrotemplate` to display a template before or after the query results. These can only be used in combination with the `template` paramter 

## Examples

Using a template can provide means to individually style the result output, for example a simple list can be turned into a phabricator task list.

```
{{#ask: [[Category:Task]]
 ...
 |outlineproperties=Severity
 |template=phab-view
}}
```

![image](https://user-images.githubusercontent.com/1245473/51059660-d2826b00-15e4-11e9-8ff3-bb1591b04e81.png)

```
{{#ask: [[Category:Task]]
 ...
 |outlineproperties=Severity, Assigned to
 |template=phab-view
}}
```

![image](https://user-images.githubusercontent.com/1245473/51059791-52103a00-15e5-11e9-85cf-86c503a10b55.png)
