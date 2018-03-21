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
      <div id="myDiagramDiv" style="height: 720px;margin:-90px 0 0 -35px"></div>
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
            {"key":-1, "category":"Start", "loc":"-393.99999999999983 48.999999999999986", "text":"开始"},
            {"category":"End", "text":"完成", "key":-16, "loc":"104.00000000000017 499.2833404541019"},
            {"text":"销售申请", "key":-2, "loc":"-259 49.25"},
            {"text":"产品报价", "key":-4, "loc":"-107 49.25"},
            {"text":"审核", "figure":"Diamond", "key":-3, "loc":"-107 147.25"},
            {"text":"资源报价", "key":-6, "loc":"105 146.25"},
            {"text":"采购报价", "key":-7, "loc":"-107 247.25"},
            {"text":"产品审核确认", "key":-8, "loc":"105 247.25"},
            {"text":"审核", "figure":"Diamond", "key":-9, "loc":"105 332.25"},
            {"text":"询价完成", "key":-10, "loc":"104 425.25"}
        ],
        "linkDataArray": [
            {"from":-1, "to":-2, "fromPort":"R", "toPort":"L", "points":[-373.99999999999983,48.999999999999986,-363.99999999999983,48.999999999999986,-332.9843749999999,48.999999999999986,-332.9843749999999,49.25,-301.96875,49.25,-291.96875,49.25]},
            {"from":-2, "to":-4, "fromPort":"R", "toPort":"L", "points":[-226.03125,49.25,-216.03125,49.25,-183,49.25,-183,49.25,-149.96875,49.25,-139.96875,49.25]},
            {"from":-4, "to":-3, "fromPort":"B", "toPort":"T", "points":[-107,64.25,-107,74.25,-107,91,-107,91,-107,107.75,-107,117.75]},
            {"from":-3, "to":-2, "fromPort":"L", "toPort":"L", "visible":true, "points":[-147.96875,147.25,-157.96875,147.25,-302,147.25,-302,98.25,-301.96875,98.25,-301.96875,49.25,-291.96875,49.25], "text":"退回"},
            {"from":-3, "to":-6, "fromPort":"R", "toPort":"L", "visible":true, "points":[-66.03125,147.25,-56.03125,147.25,3,147.25,3,146.25,62.03125,146.25,72.03125,146.25], "text":"转"},
            {"from":-3, "to":-7, "fromPort":"B", "toPort":"T", "visible":true, "points":[-107,176.75,-107,186.75,-107,204.5,-107,204.5,-107,222.25,-107,232.25], "text":"转"},
            {"from":-6, "to":-8, "fromPort":"B", "toPort":"T", "points":[105,161.25,105,171.25,105,196.75,105,196.75,105,222.25,105,232.25]},
            {"from":-7, "to":-8, "fromPort":"R", "toPort":"L", "points":[-74.03125,247.25,-64.03125,247.25,-7.1171875,247.25,-7.1171875,247.25,49.796875,247.25,59.796875,247.25]},
            {"from":-8, "to":-9, "fromPort":"B", "toPort":"T", "points":[105,262.25,105,272.25,105,282.5,105,282.5,105,292.75,105,302.75]},
            {"from":-9, "to":-6, "fromPort":"R", "toPort":"R", "visible":true, "points":[145.96875,332.25,155.96875,332.25,176,332.25,176,146.25,147.96875,146.25,137.96875,146.25], "text":"转"},
            {"from":-9, "to":-7, "fromPort":"L", "toPort":"L", "visible":true, "points":[64.03125,332.25,54.03125,332.25,-172,332.25,-172,247.25,-149.96875,247.25,-149.96875,247.25,-139.96875,247.25], "text":"转"},
            {"from":-9, "to":-10, "fromPort":"B", "toPort":"T", "visible":true, "points":[105,361.75,105,371.75,105,386,104,386,104,400.25,104,410.25], "text":"通过"},
            {"from":-3, "to":-10, "fromPort":"L", "toPort":"L", "visible":true, "points":[-147.96875,147.25,-157.96875,147.25,-226,147.25,-226,425.25,61.03125,425.25,71.03125,425.25], "text":"通过"},
            {"from":-10, "to":-16, "fromPort":"B", "toPort":"T", "points":[104,440.25,104,450.25,104,459.7666702270509,104.00000000000017,459.7666702270509,104.00000000000017,469.2833404541018,104.00000000000017,479.2833404541018]}
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
