SRF jqPlot v1.8 includes a major re-factoring effort in how SRF communicates with the jqPlot plug-in and now requires the ResourceLoader to work with. SRF modules such as jqPlot, D3 and JitGraph are now share similar parameter definitions and PHP methods which should keep maintenance easier.

SRF jqPlot now comes with 5 chart/scale renderer (pie, bar, line, bubble and donut) 

## Major change

Beginning with SRF 1.8 inline JavaScript is generally discouraged in favour of MW's ResourceLoader module therefore all JavaScript have been transferred to corresponding resource files (ext.srf.jqplot....).  jqPlot data objects are using mw.config.set/mw.config.get to get access to embedded data objects.  

## New features 

* The parameter [renderer] has been introduced to select available jqPlot renderer (pie, donut graph and bar, line graph).
* All jqPot formats can use the parameter [charttext] to display explanatory text at the bottom of each chart.
* The parameter [chartclass] can be used to assign an additional css class to change the position or add/change margin settings of a chart (float:right/float:left etc.).
* The parameter [valueformat] has been introduced to allow individual value formatting. (%d a signed integer is the default setting but %.2f would round numbers to 2 digits after decimal point and can be combined notions like EUR %.2f, $ %.2f )
* Themes (parameter [theme]) now can be customized using jqPlot themeEngine and are available for all jqPlot renderer in ext.srf.jqplot.themes.js. A colorscheme (parameter[colorscheme]) has been introduced using patterns from colorCombs and color scales developed by Cynthia Brewer.  (D3 and jPlot share the same colorschemes).
* Additional parameters are [smoothlines], [highlighter], [ticklabels]
* An experimental new format (jqplotseries, SRF_jqPlotSeries.php) has been added to allow numercial data sets to be displayed as a chart series. Stacked bar or line series are supported as well. The jqplotseries does work but need thorough testing and should not be used in production. (If you get in trouble with this module, please try to fixed yourself first.) 

## Minor changes 
The split between JavaScript and PHP made changes necessary to ensure consistency with the data model and despite our efforts we tried to avoid any disruption of existing default behaviour but in some instances it could not have been avoided.

The following jqPlot Pie parameters have been changed and could result in a slightly different display behaviour.

* [datalabeltype] has been deprecated and is replaced by [datalabels] which will have either none or 'percent','value', 'label' option
* [legendlocation] has been deprecated and is replaced by [chartlegend] which now combines the selection.

The following jqPlot Bar parameters have been changed.

* [barcolor] has been deprecated and is replaced by chartcolor (a shared parameter with other chart modules)    

## Plug-In
The plug-in has been updated to version 1.0.0b2_r947.

## Customizing and settings 
Specific customizing can be found [here](https://github.com/mwjames/smw-srfjqplot/wiki/Customizing).

Other information can be found (here)[http://semantic-mediawiki.org/wiki/Help:jqplotpie_format] and (here)[http://semantic-mediawiki.org/wiki/Help:jqplotbar_format].

### Examples

* [Bar and line chart](https://github.com/mwjames/smw-srfjqplot/wiki/Bar-and-line-chart)
* [Pie and donut chart](https://github.com/mwjames/smw-srfjqplot/wiki/Pie-and-donut-chart)
* [Series chart](https://github.com/mwjames/smw-srfjqplot/wiki/Series-chart)

## Unit tests
SWM 1.7.1 and SRF 1.7.1 and MW 1.18, MW 1.19.0beta

* Chrome 11.0.700.3 and 17.0.963.66 m
* IE 9
* Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.23) Gecko/20110920 Firefox/3.6.23 ( .NET CLR 3.5.30729; .NET4.0C)

## Deployment 
A deployment is planned together with [SMW SRF 1.8](http://www.semantic-mediawiki.org/wiki/Semantic_Result_Formats).

## Support
The community is always free to make enhancements beyond what is currently implemented, please feel free to add any features on your own.

