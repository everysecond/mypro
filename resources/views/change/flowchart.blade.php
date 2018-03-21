<!DOCTYPE html>
<html>
<head>
    <title>Flowchart</title>
    <meta name="description" content="Interactive flowchart diagram implemented by GoJS in JavaScript for HTML."/>
    <!-- Copyright 1998-2016 by Northwoods Software Corporation. -->
    <meta charset="UTF-8">
    <script src="/js/go.js"></script>
   {{-- <link href="/css/goSamples.css" rel="stylesheet" type="text/css"/>--}}  <!-- you don't need to use this -->
    <script id="code">
        function init() {
            //if (window.goSamples) goSamples();  // init for these samples -- you don't need to call this
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
                        //isShadowed: true,
                        //shadowColor: "#888",
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
                            $(go.Shape, "RoundedRectangle",
                                    { stroke: "#FF9933", strokeWidth: 2, fill: "#ff6600" }),
                            $(go.TextBlock,
                                    {
                                        margin: 3,
                                        maxSize: new go.Size(130, NaN),
                                        wrap: go.TextBlock.WrapFit,
                                        textAlign: "center",
                                        editable: true,
                                        font: "bold 8pt Helvetica, Arial, sans-serif",
                                        stroke: lightText
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
      <div id="myDiagramDiv" style="height: 720px;margin:-150px 0 0 -30px"></div>
    </span>
    </div>
    <p>

    </p>
    <p>
    </p>
    <input type="hidden" id="currentStatus" value="{{$currentStatus}}">
    <input type="hidden" id="nextOperater" value="{{$nextOperater}}">
  <textarea id="mySavedModel"  style="display: none;">
  </textarea>
</div>
<script>
    var txt = { "class": "go.GraphLinksModel",
                "linkFromPortIdProperty": "fromPort",
                "linkToPortIdProperty": "toPort",
                "nodeDataArray": [
                    {"key":-1, "category":"Start", "loc":"-393.99999999999983 48.999999999999986", "text":"开始"},
                    {"text":"申请变更", "key":-2, "loc":"-325.9999999999999 48.833328247070284", "stroke":"#ffffff", "fill":"#FFFFFF"},
                    {"text":"可行性审批", "key":-3, "loc":"-248 82.83332824707038"},
                    {"text":"审批", "figure":"Diamond", "key":-4, "loc":"-248.00000000000006 146.83332824707014"},
                    {"text":"变更驳回", "category":"-4", "key":-5, "loc":"-248.00000000000006 231.8333282470703"},
                    {"text":"变更方案规划", "key":-6, "loc":"-105.99999999999997 146.83332824707037"},
                    {"text":"实施/回退方案制定", "key":-7, "loc":"31.99999999999986 146.83332824707037"},
                    {"text":"方案测试", "key":-9, "loc":"141.00000000000009 147.28334045410145"},
                    {"text":"测试", "figure":"Diamond", "key":-10, "loc":"140.99999999999977 210.28334045410145"},
                    {"text":"方案及测试结果审批", "key":-11, "loc":"141.00000000000006 289.28334045410145"},
                    {"text":"审核", "figure":"Diamond", "key":-12, "loc":"140.99999999999983 352.28334045410145"},
                    {"text":"变更发布实施", "key":-13, "loc":"44.00000000000003 401.283340454102"},
                    {"text":"变更结果验证", "key":-14, "loc":"-146.9999999999999 401.283340454102"},
                    {"text":"分析", "figure":"Diamond", "key":-15, "loc":"-327.99999999999994 279.28334045410156"},
                    {"category":"End", "text":"完成", "key":-16, "loc":"-393.9999999999998 401.2833404541018"}
                ],
                "linkDataArray": [
                    {"from":-3, "to":-4, "fromPort":"B", "toPort":"T", "points":[-248,97.83332824707036,-248,107.83332824707036,-248,107.83332824707036,-248,107.33332824707017,-248,107.33332824707017,-248,117.33332824707017]},
                    {"from":-4, "to":-5, "fromPort":"B", "toPort":"T", "visible":true, "points":[-248,176.33332824707017,-248,186.33332824707017,-248,196.5833282470703,-248,196.5833282470703,-248,206.83332824707043,-248,216.83332824707043], "text":"不通过"},
                    {"from":-4, "to":-6, "fromPort":"R", "toPort":"L", "visible":true, "points":[-207.03125,146.83332824707017,-197.03125,146.83332824707017,-179.1171875,146.83332824707017,-179.1171875,146.83332824707026,-161.20312500000003,146.83332824707026,-151.20312500000003,146.83332824707026], "text":"通过"},
                    {"from":-6, "to":-7, "fromPort":"R", "toPort":"L", "points":[-60.79687500000003,146.83332824707026,-50.79687500000003,146.83332824707026,-43.867187500000085,146.83332824707026,-43.867187500000085,146.83332824707026,-36.93750000000014,146.83332824707026,-26.937500000000142,146.83332824707026]},
                    {"from":-7, "to":-9, "fromPort":"R", "toPort":"L", "points":[90.93749999999986,146.83332824707026,100.93749999999986,146.83332824707026,100.93749999999986,147.05833435058588,98.03125000000011,147.05833435058588,98.03125000000011,147.28334045410153,108.03125000000011,147.28334045410153]},
                    {"from":-9, "to":-10, "fromPort":"B", "toPort":"T", "points":[141.0000000000001,162.28334045410153,141.0000000000001,172.28334045410153,140.99999999999997,172.28334045410153,140.99999999999997,170.78334045410145,140.99999999999983,170.78334045410145,140.99999999999983,180.78334045410145]},
                    {"from":-10, "to":-11, "fromPort":"B", "toPort":"T", "visible":true, "points":[140.99999999999983,239.78334045410145,140.99999999999983,249.78334045410145,140.99999999999983,257.0333404541015,141.00000000000006,257.0333404541015,141.00000000000006,264.28334045410156,141.00000000000006,274.28334045410156], "text":"通过"},
                    {"from":-10, "to":-7, "fromPort":"L", "toPort":"L", "visible":true, "points":[100.03124999999983,210.28334045410145,90.03124999999983,210.28334045410145,-46,210.28334045410145,-46,146.83332824707026,-36.93750000000014,146.83332824707026,-26.937500000000142,146.83332824707026], "text":"不通过"},
                    {"from":-11, "to":-12, "fromPort":"B", "toPort":"T", "points":[141.00000000000006,304.28334045410156,141.00000000000006,314.28334045410156,140.99999999999994,314.28334045410156,140.99999999999994,312.78334045410145,140.99999999999983,312.78334045410145,140.99999999999983,322.78334045410145]},
                    {"from":-12, "to":-7, "fromPort":"L", "toPort":"L", "visible":true, "points":[100.03124999999983,352.28334045410145,90.03124999999983,352.28334045410145,-46,352.28334045410145,-46,146.83332824707026,-36.93750000000014,146.83332824707026,-26.937500000000142,146.83332824707026], "text":"不通过"},
                    {"from":-12, "to":-13, "fromPort":"B", "toPort":"R", "visible":true, "points":[140.99999999999983,381.78334045410145,140.99999999999983,391.78334045410145,140.99999999999983,401.2833404541018,120.10156249999993,401.2833404541018,99.20312500000003,401.2833404541018,89.20312500000003,401.2833404541018], "text":"通过"},
                    {"from":-13, "to":-14, "fromPort":"L", "toPort":"R", "points":[-1.2031249999999716,401.2833404541018,-11.203124999999972,401.2833404541018,-51.49999999999997,401.2833404541018,-51.49999999999997,401.2833404541018,-91.79687499999997,401.2833404541018,-101.79687499999997,401.2833404541018]},
                    {"from":-2, "to":-3, "fromPort":"R", "toPort":"T", "points":[-293.0312499999999,48.833328247070284,-283.0312499999999,48.833328247070284,-248,48.833328247070284,-248,53.33332824707032,-248,57.833328247070355,-248,67.83332824707036]},
                    {"from":-15, "to":-16, "fromPort":"B", "toPort":"T", "visible":true, "points":[-327.99999999999994,308.78334045410145,-327.99999999999994,318.78334045410145,-327.99999999999994,345.0333404541016,-393.99999999999983,345.0333404541016,-393.99999999999983,371.2833404541018,-393.99999999999983,381.2833404541018], "text":"不通过"},
                    {"from":-14, "to":-16, "fromPort":"L", "toPort":"R", "points":[-192.20312499999997,401.2833404541018,-202.20312499999997,401.2833404541018,-283.1015624999999,401.2833404541018,-283.1015624999999,401.2833404541018,-363.99999999999983,401.2833404541018,-373.99999999999983,401.2833404541018]},
                    {"from":-5, "to":-15, "fromPort":"B", "toPort":"R", "points":[-248,246.83332824707043,-248,256.8333282470704,-248,279.28334045410145,-262.515625,279.28334045410145,-277.03124999999994,279.28334045410145,-287.03124999999994,279.28334045410145]},
                    {"from":-15, "to":-3, "fromPort":"L", "toPort":"L", "visible":true, "points":[-368.96874999999994,279.28334045410145,-378.96874999999994,279.28334045410145,-393,279.28334045410145,-393,82.83332824707036,-297.0859375,82.83332824707036,-287.0859375,82.83332824707036], "text":"通过"},
                    {"from":-1, "to":-2, "fromPort":"R", "toPort":"L", "points":[-371.77569828477016,49,-361.77569828477016,49,-361.77569828477016,48.916664123535156,-368.96875,48.916664123535156,-368.96875,48.83332824707031,-358.96875,48.83332824707031]}
                ]}
            ;
    var status = document.getElementById("currentStatus").value;
    var comArr = new Array(),keyArr = new Array();
    comArr["可行性审批"] = "-124 82.25";
    comArr["变更方案规划"] = "-109 113.25";
    comArr["实施/回退方案制定"] = "33 114.25";
    comArr["方案测试"] = "143 94.25";
    comArr["方案及测试结果审批"] = "30 325.25";
    comArr["变更发布实施"] = "45 433.25";
    comArr["变更结果验证"] = "-148 434.25";
    comArr["变更驳回"] = "-146.9999999999999 232.2500000000001";
    keyArr["可行性审批"] = -3;
    keyArr["变更方案规划"] = -6;
    keyArr["实施/回退方案制定"] = -7;
    keyArr["方案测试"] = -9;
    keyArr["方案及测试结果审批"] = -11;
    keyArr["变更发布实施"] = -13;
    keyArr["变更结果验证"] = -14;
    keyArr["变更驳回"] = -5;
    var nextOperater = document.getElementById("nextOperater").value;
    for (var i = 0; i < txt.nodeDataArray.length; i++) {
        if (txt.nodeDataArray[i].text == status) {
            txt.nodeDataArray[i].category = "Current";
            if(nextOperater !=""){
                txt.nodeDataArray[txt.nodeDataArray.length] ={"category":"Comment", "text":nextOperater, "key":-17, "loc":comArr[status]};
                txt.linkDataArray[txt.nodeDataArray.length] ={from: "-17", to: keyArr[status], category: "Comment"};
            }
        }
    }

    txt = JSON.stringify(txt);
    document.getElementById("mySavedModel").value = txt;
</script>
</body>
</html>
