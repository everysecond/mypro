<!DOCTYPE html>
<html>
<head>
    <title>Flowchart</title>
    <meta name="description" content="Interactive flowchart diagram implemented by GoJS in JavaScript for HTML."/>
    <!-- Copyright 1998-2016 by Northwoods Software Corporation. -->
    <meta charset="UTF-8">
    <script src="/js/go.js"></script>
    <script id="code">
        function init() {
            var $ = go.GraphObject.make;  // for conciseness in defining templates
            myDiagram =
                    $(go.Diagram, "myDiagramDiv",  // must name or refer to the DIV HTML element
                            {
                                initialContentAlignment: go.Spot.Center,
                                allowDrop: true,  // must be true to accept drops from the Palette
                                "LinkDrawn": showLinkLabel,  // this DiagramEvent listener is defined below
                                "LinkRelinked": showLinkLabel,
                                "animationManager.duration": 800, // slightly longer than default (600ms) animation
                                "undoManager.isEnabled": true  // enable undo & redo
                            });

            // when the document is modified, add a "*" to the title and enable the "Save" button
            myDiagram.addDiagramListener("Modified", function (e) {
                var button = document.getElementById("SaveButton");
                if (button) button.disabled = !myDiagram.isModified;
                var idx = document.title.indexOf("*");
                if (myDiagram.isModified) {
                    if (idx < 0) document.title += "*";
                } else {
                    if (idx >= 0) document.title = document.title.substr(0, idx);
                }
            });

            // helper definitions for node templates

            function nodeStyle() {
                return [
                    // The Node.location comes from the "loc" property of the node data,
                    // converted by the Point.parse static method.
                    // If the Node.location is changed, it updates the "loc" property of the node data,
                    // converting back using the Point.stringify static method.
                    new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
                    {
                        // the Node.location is at the center of each node
                        locationSpot: go.Spot.Center,
                        // handle mouse enter/leave events to show/hide the ports
                        mouseEnter: function (e, obj) {
                            showPorts(obj.part, true);
                        },
                        mouseLeave: function (e, obj) {
                            showPorts(obj.part, false);
                        }
                    }
                ];
            }

            // Define a function for creating a "port" that is normally transparent.
            // The "name" is used as the GraphObject.portId, the "spot" is used to control how links connect
            // and where the port is positioned on the node, and the boolean "output" and "input" arguments
            // control whether the user can draw links from or to the port.
            function makePort(name, spot, output, input) {
                // the port is basically just a small circle that has a white stroke when it is made visible
                return $(go.Shape, "Circle",
                        {
                            fill: "transparent",
                            stroke: null,  // this is changed to "white" in the showPorts function
                            desiredSize: new go.Size(8, 8),
                            alignment: spot, alignmentFocus: spot,  // align the port on the main Shape
                            portId: name,  // declare this object to be a "port"
                            fromSpot: spot, toSpot: spot,  // declare where links may connect at this port
                            fromLinkable: output, toLinkable: input,  // declare whether the user may draw links to/from here
                            cursor: "pointer"  // show a different cursor to indicate potential link point
                        });
            }
            // define the Node templates for regular nodes
            var lightText = 'whitesmoke';
            myDiagram.nodeTemplateMap.add("",  // the default category
                    $(go.Node, "Spot", nodeStyle(),
                            // the main object is a Panel that surrounds a TextBlock with a rectangular Shape
                            $(go.Panel, "Auto",
                                    $(go.Shape, "RoundedRectangle",
                                            {fill: "#19b492", stroke: null},
                                            new go.Binding("figure", "figure")),
                                    $(go.TextBlock,
                                            {
                                                font: "bold 8pt Helvetica, Arial, sans-serif",
                                                stroke: lightText,
                                                margin: 8,
                                                maxSize: new go.Size(160, NaN),
                                                wrap: go.TextBlock.WrapFit,
                                                editable: true
                                            },
                                            new go.Binding("text").makeTwoWay())
                            ),
                            // four named ports, one on each side:
                            makePort("T", go.Spot.Top, false, true),
                            makePort("L", go.Spot.Left, true, true),
                            makePort("R", go.Spot.Right, true, true),
                            makePort("B", go.Spot.Bottom, true, false)
                    ));

            myDiagram.nodeTemplateMap.add("Current",
                    $(go.Node, "Spot", nodeStyle(),
                            $(go.Panel, "Auto",
                                    $(go.Shape, "RoundedRectangle",
                                            {minSize: new go.Size(30, 35), fill: "#D70041", stroke: null}),
                                    $(go.TextBlock, "",
                                            {font: "bold 8pt Helvetica, Arial, sans-serif", stroke: lightText},
                                            new go.Binding("text"))
                            ),
                            // three named ports, one on each side except the top, all output only:
                            makePort("L", go.Spot.Left, true, false),
                            makePort("R", go.Spot.Right, true, false),
                            makePort("B", go.Spot.Bottom, true, false)
                    ));


            myDiagram.nodeTemplateMap.add("Start",
                    $(go.Node, "Spot", nodeStyle(),
                            $(go.Panel, "Auto",
                                    $(go.Shape, "Circle",
                                            {minSize: new go.Size(40, 40), fill: "#79C900", stroke: null}),
                                    $(go.TextBlock, "Start",
                                            {font: "bold 8pt Helvetica, Arial, sans-serif", stroke: lightText},
                                            new go.Binding("text"))
                            ),
                            // three named ports, one on each side except the top, all output only:
                            makePort("L", go.Spot.Left, true, false),
                            makePort("R", go.Spot.Right, true, false),
                            makePort("B", go.Spot.Bottom, true, false)
                    ));

            myDiagram.nodeTemplateMap.add("End",
                    $(go.Node, "Spot", nodeStyle(),
                            $(go.Panel, "Auto",
                                    $(go.Shape, "Circle",
                                            {minSize: new go.Size(40, 40), fill: "#DC3C00", stroke: null}),
                                    $(go.TextBlock, "End",
                                            {font: "bold 8pt Helvetica, Arial, sans-serif", stroke: lightText},
                                            new go.Binding("text"))
                            ),
                            // three named ports, one on each side except the bottom, all input only:
                            makePort("T", go.Spot.Top, false, true),
                            makePort("L", go.Spot.Left, false, true),
                            makePort("R", go.Spot.Right, false, true)
                    ));

            myDiagram.nodeTemplateMap.add("Comment",
                    $(go.Node, "Auto", nodeStyle(),
                            $(go.Shape, "File",
                                    {fill: "#EFFAB4", stroke: null}),
                            $(go.TextBlock,
                                    {
                                        margin: 5,
                                        maxSize: new go.Size(200, NaN),
                                        wrap: go.TextBlock.WrapFit,
                                        textAlign: "center",
                                        editable: true,
                                        font: "bold 8pt Helvetica, Arial, sans-serif",
                                        stroke: '#454545'
                                    },
                                    new go.Binding("text").makeTwoWay())
                            // no ports, because no links are allowed to connect with a comment
                    ));


            // replace the default Link template in the linkTemplateMap
            myDiagram.linkTemplate =
                    $(go.Link,  // the whole link panel
                            {
                                routing: go.Link.AvoidsNodes,
                                curve: go.Link.JumpOver,
                                corner: 5, toShortLength: 4,
                                relinkableFrom: true,
                                relinkableTo: true,
                                reshapable: true,
                                resegmentable: true,
                                // mouse-overs subtly highlight links:
                                mouseEnter: function (e, link) {
                                    link.findObject("HIGHLIGHT").stroke = "rgba(30,144,255,0.2)";
                                },
                                mouseLeave: function (e, link) {
                                    link.findObject("HIGHLIGHT").stroke = "transparent";
                                }
                            },
                            new go.Binding("points").makeTwoWay(),
                            $(go.Shape,  // the highlight shape, normally transparent
                                    {isPanelMain: true, strokeWidth: 8, stroke: "transparent", name: "HIGHLIGHT"}),
                            $(go.Shape,  // the link path shape
                                    {isPanelMain: true, stroke: "gray", strokeWidth: 2}),
                            $(go.Shape,  // the arrowhead
                                    {toArrow: "standard", stroke: null, fill: "gray"}),
                            $(go.Panel, "Auto",  // the link label, normally not visible
                                    {visible: false, name: "LABEL", segmentIndex: 2, segmentFraction: 0.5},
                                    new go.Binding("visible", "visible").makeTwoWay(),
                                    $(go.Shape, "RoundedRectangle",  // the label shape
                                            {fill: "#F8F8F8", stroke: null}),
                                    $(go.TextBlock, "Yes",  // the label
                                            {
                                                textAlign: "center",
                                                font: "10pt helvetica, arial, sans-serif",
                                                stroke: "#333333",
                                                editable: true
                                            },
                                            new go.Binding("text").makeTwoWay())
                            )
                    );

            // Make link labels visible if coming out of a "conditional" node.
            // This listener is called by the "LinkDrawn" and "LinkRelinked" DiagramEvents.
            function showLinkLabel(e) {
                var label = e.subject.findObject("LABEL");
                if (label !== null) label.visible = (e.subject.fromNode.data.figure === "Diamond");
            }

            // temporary links used by LinkingTool and RelinkingTool are also orthogonal:
            myDiagram.toolManager.linkingTool.temporaryLink.routing = go.Link.Orthogonal;
            myDiagram.toolManager.relinkingTool.temporaryLink.routing = go.Link.Orthogonal;

            load();  // load an initial diagram from some JSON text

            // initialize the Palette that is on the left side of the page
            myPalette =
                    $(go.Palette, "myPaletteDiv",  // must name or refer to the DIV HTML element
                            {
                                "animationManager.duration": 800, // slightly longer than default (600ms) animation
                                nodeTemplateMap: myDiagram.nodeTemplateMap,  // share the templates used by myDiagram
                                model: new go.GraphLinksModel([  // specify the contents of the Palette
                                    {category: "Start", text: "Start"},
                                    {text: "Step"},
                                    {text: "???", figure: "Diamond"},
                                    {category: "End", text: "End"},
                                    {category: "Comment", text: "Comment"}
                                ])
                            });

        }

        // Make all ports on a node visible when the mouse is over the node
        function showPorts(node, show) {
            var diagram = node.diagram;
            if (!diagram || diagram.isReadOnly || !diagram.allowLink) return;
            node.ports.each(function (port) {
                port.stroke = (show ? "white" : null);
            });
        }


        // Show the diagram's model in JSON format that the user may edit
        function save() {
            document.getElementById("mySavedModel").value = myDiagram.model.toJson();
            myDiagram.isModified = false;
        }
        function load() {
            myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
        }

        // add an SVG rendering of the diagram at the end of this page
        function makeSVG() {
            var svg = myDiagram.makeSvg({
                scale: 0.5
            });
            svg.style.border = "1px solid black";
            obj = document.getElementById("SVGArea");
            obj.appendChild(svg);
            if (obj.children.length > 0) {
                obj.replaceChild(svg, obj.children[0]);
            }
        }
    </script>
</head>
<body onload="init()">
<div id="">
    <div style="width:100%; white-space:nowrap;">
    <span style="display: inline-block; vertical-align: top; padding: 5px; width:100%">
      <div id="myDiagramDiv" style="height: 720px;margin:-155px 0 0 -35px"></div>
    </span>
    </div>
    <p>

    </p>
    <p>
    </p>
    <input type="hidden" id="currentStatus" value="{{$currentStatus}}">
  <textarea id="mySavedModel" 　 style="display: none;">
  </textarea>
</div>
<script>
    var txt = { "class": "go.GraphLinksModel",
        "linkFromPortIdProperty": "fromPort",
        "linkToPortIdProperty": "toPort",
        "nodeDataArray": [
            {"category":"Start", "text":"开始", "key":-1, "loc":"-523.0000000000003 119.24999999999999"},
            {"text":"提交问题申请", "key":-2, "loc":"-413.9999999999999 119.25000000000004"},
            {"text":"审核", "key":-3, "loc":"-347.00000000000006 194.24999999999991"},
            {"text":"存在问题", "figure":"Diamond", "key":-4, "loc":"-194.9999999999999 267.2500000000001"},
            {"text":"问题分析及解决方案制定", "key":-5, "loc":"29.822500000000133 268.09749999999985"},
            {"text":"驳回问题", "key":-6, "loc":"-356.1274066247782 266.68629668761054"},
            {"text":"提交", "figure":"Diamond", "key":-7, "loc":"-461.907029478458 266.6672335600906"},
            {"category":"End", "text":"完成", "key":-8, "loc":"-529.9999999999997 488.2499999999999"},
            {"text":"确定执行方案", "key":-9, "loc":"29.722178649354774 343.91032135064467"},
            {"text":"解决", "figure":"Diamond", "key":-10, "loc":"29.554749454517022 434.04499999999996"},
            {"text":"关闭问题", "key":-11, "loc":"-293.99999999999926 488.24999999999943"}
        ],
        "linkDataArray": [
            {"from":-1, "to":-2, "fromPort":"R", "toPort":"L", "points":[-499.244186046512,119.24999999999999,-489.244186046512,119.24999999999999,-484.87209302325584,119.24999999999999,-484.87209302325584,119.25000000000006,-480.49999999999966,119.25000000000006,-470.49999999999966,119.25000000000006]},
            {"from":-2, "to":-3, "fromPort":"R", "toPort":"T", "points":[-357.4999999999999,119.25000000000004,-347.4999999999999,119.25000000000004,-347.00000000000017,119.25000000000004,-347.00000000000017,143.275,-347.00000000000017,167.29999999999995,-347.00000000000017,177.29999999999995]},
            {"from":-3, "to":-4, "fromPort":"R", "toPort":"T", "points":[-322.50000000000017,194.24999999999994,-312.50000000000017,194.24999999999994,-194.9999999999999,194.24999999999994,-194.9999999999999,209.05,-194.9999999999999,223.8500000000001,-194.9999999999999,233.8500000000001]},
            {"from":-4, "to":-5, "fromPort":"R", "toPort":"L", "visible":true, "points":[-114.49999999999989,267.2500000000001,-104.49999999999989,267.2500000000001,-86.58874999999986,267.2500000000001,-86.58874999999986,268.0974999999999,-68.67749999999984,268.0974999999999,-58.67749999999984,268.0974999999999], "text":"是"},
            {"from":-4, "to":-6, "fromPort":"L", "toPort":"R", "visible":true, "points":[-275.4999999999999,267.2500000000001,-285.4999999999999,267.2500000000001,-295.563703312389,267.2500000000001,-295.563703312389,266.6862966876107,-305.62740662477813,266.6862966876107,-315.62740662477813,266.6862966876107], "text":"否"},
            {"from":-9, "to":-10, "fromPort":"B", "toPort":"T", "points":[29.72217864935473,360.8603213506451,29.72217864935473,370.8603213506451,29.72217864935473,380.7526606753226,29.554749454517093,380.7526606753226,29.554749454517093,390.6450000000001,29.554749454517093,400.6450000000001]},
            {"from":-10, "to":-11, "fromPort":"B", "toPort":"R", "visible":true, "points":[29.554749454517093,467.4450000000001,29.554749454517093,477.4450000000001,29.554749454517093,488.2499999999998,-106.97262527274117,488.2499999999998,-243.49999999999943,488.2499999999998,-253.49999999999943,488.2499999999998], "text":"是"},
            {"from":-11, "to":-8, "fromPort":"L", "toPort":"R", "points":[-334.49999999999926,488.24999999999943,-344.49999999999926,488.24999999999943,-420.3720930232554,488.24999999999943,-420.3720930232554,488.2500000000001,-496.2441860465116,488.2500000000001,-506.2441860465116,488.2500000000001]},
            {"from":-6, "to":-7, "fromPort":"L", "toPort":"R", "points":[-396.62740662477813,266.6862966876107,-406.62740662477813,266.6862966876107,-406.62740662477813,266.67676512385066,-403.40702947845807,266.67676512385066,-403.40702947845807,266.6672335600906,-413.40702947845807,266.6672335600906]},
            {"from":-7, "to":-8, "fromPort":"B", "toPort":"T", "visible":true, "points":[-461.907029478458,300.0672335600906,-461.907029478458,310.0672335600906,-461.907029478458,382.2807098033012,-529.9999999999999,382.2807098033012,-529.9999999999999,454.49418604651174,-529.9999999999999,464.49418604651174], "text":"取消"},
            {"from":-7, "to":-3, "fromPort":"L", "toPort":"L", "visible":true, "points":[-510.407029478458,266.6672335600906,-520.4070294784581,266.6672335600906,-520.4070294784581,194.24999999999994,-450.95351473922915,194.24999999999994,-381.50000000000017,194.24999999999994,-371.50000000000017,194.24999999999994], "text":"确定"},
            {"from":-5, "to":-9, "fromPort":"B", "toPort":"T", "points":[29.82250000000016,293.4974999999999,29.82250000000016,303.4974999999999,29.82250000000016,310.2289106753225,29.72217864935473,310.2289106753225,29.72217864935473,316.9603213506451,29.72217864935473,326.9603213506451]},
            {"from":-10, "to":-5, "fromPort":"L", "toPort":"L", "visible":true, "points":[-18.945250545482907,434.0450000000001,-28.945250545482907,434.0450000000001,-68.67749999999984,434.0450000000001,-68.67749999999984,351.07124999999996,-68.67749999999984,268.0974999999999,-58.67749999999984,268.0974999999999], "text":"否"}
        ]};
    var status = document.getElementById("currentStatus").value;
    for (var i = 0; i < txt.nodeDataArray.length; i++) {
        if (txt.nodeDataArray[i].text == status) {
            txt.nodeDataArray[i].category = "Current";
        }
    }
    txt = JSON.stringify(txt);
    document.getElementById("mySavedModel").value = txt;
</script>
</body>
</html>
