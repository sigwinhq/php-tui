---
title: Grid
description: The grid is a widget that provides either a horiztonal or vertical _layout_  based on a series of constraints.  Widgets can be supplied to fill the cells corresponding to the resolved constraints.
---
## Grid

The grid is a widget that provides either a horiztonal or vertical _layout_  based on a series of constraints.  Widgets can be supplied to fill the cells corresponding to the resolved constraints.
{{% terminal file="/data/example/docs/widget/grid.html" %}}
{{< details "Show code"  >}}
{{% codeInclude file="/data/example/docs/widget/grid.php" language="php" %}}

{{< /details >}}
### Parameters

Configure the widget using the builder methods named as follows:

| Name | Type | Description |
| --- | --- | --- |
| **direction** | `PhpTui\Tui\Model\Direction` | The direction of the grid |
| **widgets** | `list<\PhpTui\Tui\Model\Widget>` | The widgets. There should be at least as many constraints as widgets. |
| **constraints** | `list<\PhpTui\Tui\Model\Constraint>` | The constraints define the widget (Direction::Horizontal) or height(Direction::Vertical) of the cells. |