# Parameters

Parameters to the `#ask` function can apply to the `filtered` format as a whole (format level) or to only one specific printout (printout level). On format level there are some generic parameters that are common to all result formats and some format specific parameters that are used only by the `filtered` format.

Consider the following query:
```
{{#ask:[[SomeCondition]]
|? SomePrintout |+filter=number
|? Position
|format=filtered
|limit=100
|views=map
|mapviewmarkerpositionproperty=Position
}}
```

In this query `limit=100` is on format level (generic), `views=map` is on format level (format specific) and `+filter=number` is on printout level.

## Format level - Generic:

Supported:
* format
* mainlabel
* sort
* order
* intro
* outro
* limit
* offset
* headers (Table view)

Not supported by the `filtered` format:
* source
* link
* searchlabel
* default

## Format level - Format specific:
* views
* filter position

### List view

* list view type
* list view template
* list view named args
* list view introtemplate
* list view outrotemplate

### Calendar view

* calendar view start
* calendar view end
* calendar view title
* calendar view title template

### Table view

* table view class

### Map view

* map view marker position property

## Printout level

* hide (List view, Calendar view only)
* align (`right`|`left`|`center`) (Table view only)

### Value filter

* value filter collapsible (`collapsed`|`uncollapsed`)
* value filter switches (`and or`)
* value filter values (list of strings)
* value filter height (HTML-compliant height value)

### Distance filter

* distance filter origin (lat lon) *Required*
* distance filter collapsible (`collapsed`|`uncollapsed`)
* distance filter initial value (number)
* distance filter max distance (number)
* distance filter unit (`m`|`km`|`mi`|`nm`)

### Number filter

* number filter collapsible (`collapsed`|`uncollapsed`)
* number filter max value (number)
* number filter min value (number)
* number filter step (number)
* number filter sliders (`min`|`max`|`select`|`range`)
* number filter label (string)

# Building

This is only required for development, not for simple installation and usage.

From the `.../SemanticResultFormats/formats/filtered` directory run
 ```
 npm install
 ```
