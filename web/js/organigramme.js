
/*******************************************************************************************************
 *
 * ATTENTION : la lecture du code situé après ce commentaire peut entrainer des saignements de nez...
 *
 ******************************************************************************************************/

$(document).ready(function() {
    var treeData = treeData || {};

    // nombre de noeud et taille max du texte
    var totalNodes = 0, maxLabelLength = 0;

    // variables pour garder les noueuds entre événements
    var selectedNode = null, draggingNode = null, clickedNode = null;
    var i = 0, duration = 750, root;

    // TAILLE DE L'ARBO
    var margin = { top: 20, right: 120, bottom: 20, left: 120 };
    var viewerWidth = $("#droite").width() - 100;
    var postool = $("#toolbar").offset();
    var posfoot = $("#footer").offset();

    var viewerHeight = posfoot.top - postool.top + $("#toolbar").height();

    var tree = d3.layout.tree().size([viewerHeight, viewerWidth]);

    // pour les liens
    var diagonal = d3.svg.diagonal().projection(function (d) {
        return [d.y, d.x];
    });

    // Fonction récursive pour parcourir les noeuds et leur appliquer des params (ou fonctions) :)
    function visit(parent, visitFn, childrenFn) {
        if (!parent) return;
        visitFn(parent);
        var children = childrenFn(parent);
        if (children) {
            var count = children.length;
            for (var i = 0; i < count; i++) {
                visit(children[i], visitFn, childrenFn);
            }
        }
    }

    if(treeData.length > 0) {
        visit(treeData, function (d) {
            totalNodes++;
            maxLabelLength = Math.max(d.name.length, maxLabelLength);

        }, function (d) {
            return d.children && d.children.length > 0 ? d.children : null;
        });
    }

    function initiateDrag(d, domNode) {
        draggingNode = d;
        d3.select(domNode).select('.ghostCircle').attr('pointer-events', 'none');
        d3.selectAll('.ghostCircle').attr('class', 'ghostCircle show');
        d3.select(domNode).attr('class', 'node activeDrag');

        svgGroup.selectAll("g.node").sort(function (a, b) { // select the parent and sort the path's
            if (a.id != draggingNode.id) return 1; // a is not the hovered element, send "a" to the back
            else return -1; // a is the hovered element, bring "a" to the front
        });

        // ce qui suit masque les liens enfants / parents pendant le déplacement
        // lien et noeuds enfants
        if (nodes.length > 1) {
            links = tree.links(nodes);
            nodePaths = svgGroup.selectAll("path.link")
                .data(links,function (d) {
                    return d.target.id;
                }).remove();
            nodesExit = svgGroup.selectAll("g.node")
                .data(nodes,function (d) {
                    return d.id;
                }).filter(function (d, i) {
                    if (d.id == draggingNode.id) {
                        return false;
                    }
                    return true;
                }).remove();
        }

        // liens parents
        parentLink = tree.links(tree.nodes(draggingNode.parent));
        svgGroup.selectAll('path.link').filter(function (d, i) {
            if (d.target.id == draggingNode.id) {
                return true;
            }
            return false;
        }).remove();

        dragStarted = null;
    }

    // balise svg de base avec une classe pour un peu de css si besoin
    var baseSvg = d3.select("#lecontenu").append("svg")
        .attr("width", viewerWidth)
        .attr("height", viewerHeight)
        .attr("class", "lesvg");


    /*********************************  TOOLBAR ****************************************************************************/
    d3.select("#addUser").on("click", function () {
        // construction d'un mini json pour l'élément choisi
        if (clickedNode) {
            seloption = $("#listUsers option:selected");
            if(seloption.val() != null) {
                var newJSON = new Object();
                var newNode = tree.nodes(newJSON);
                newNode.name = seloption.text();
                newNode.id = seloption.val();

                if (typeof clickedNode.children !== 'undefined' || typeof clickedNode._children !== 'undefined') {
                    if (typeof clickedNode.children !== 'undefined') {
                        clickedNode.children.push(newNode);
                    } else {
                        clickedNode._children.push(newNode);
                    }
                } else {
                    clickedNode.children = [];
                    clickedNode.children.push(newNode);
                }
                expand(clickedNode);
                seloption.remove();
                update(root);
            }
        }
        else if(root == null) {
            seloption = $("#listUsers option:selected");
            // on a rien cliqué parceque l'organigramme est vide
            treeData = {
                name: seloption.text(),
                id: seloption.val()
            };
            visit(treeData, function (d) {
                totalNodes++;
                maxLabelLength = Math.max(d.name.length, maxLabelLength);

            }, function (d) {
                return d.children && d.children.length > 0 ? d.children : null;
            });
            root = treeData;
            root.x0 = viewerHeight / 2;
            root.y0 = margin.left;
            update(root);
            seloption.remove();
            $("#toolbar").slideUp();
        }
    });

    d3.select("#btnSupprimer").on("click", function () {
        var index = clickedNode.parent.children.indexOf(clickedNode);
        if (index > -1) {
            clickedNode.parent.children.splice(index, 1);
        }
        creerOptions(clickedNode);
        update(root);
    });

    $('#picker').colpick({
        layout: 'hex',
        onSubmit: function (hsb, hex, rgb, el) {
            $(el).find(".glyphicon , .textcolor").css('color', '#' + hex);
            changeColors(clickedNode, '#' + hex);
            update(root);
            $(el).colpickHide();
        }
    });

    $("#listUsers").select2();

    function changeColors(node, selColor) {
        node.color = selColor;
        childz = node.children || node._children;
        if (childz) {
            childz.forEach(function (d) {
                changeColors(d, selColor);
            });
        }
    }

    function creerOptions(node) {
        // recrée les options dans le select
        var childz = node._children || node.children;
        if (childz) {
            childz.forEach(function (d) {
                creerOptions(d);
            });
        }
        $("<option/>").val("1").text(node.name).prependTo("#listUsers");
        childz = null;
    }

    /**************************************************** FIN FONCTIONS TOOLBAR ***************************************/





        // Define the drag listeners for drag/drop behaviour of nodes.
    dragListener = d3.behavior.drag()
        .on("dragstart", function (d) {
            if (d == root) {
                return;
            }
            dragStarted = true;
            nodes = tree.nodes(d);
            d3.event.sourceEvent.stopPropagation();
            // suppression de l'événement mouseover
        })
        .on("drag",function (d) {
            if (d == root) {
                return;
            }
            if (dragStarted) {
                domNode = this;
                initiateDrag(d, domNode);
            }

            d.x0 += d3.event.dy;
            d.y0 += d3.event.dx;
            var node = d3.select(this);
            node.attr("transform", "translate(" + d.y0 + "," + d.x0 + ")");
            updateTempConnector();
        }).on("dragend", function (d) {
            if (d == root) {
                return;
            }
            domNode = this;
            if (selectedNode) {
                // on enlève l'éménet de son ancien parent pour le rattacher à son nouveau
                var index = draggingNode.parent.children.indexOf(draggingNode);
                if (index > -1) {
                    draggingNode.parent.children.splice(index, 1);
                }
                if (typeof selectedNode.children !== 'undefined' || typeof selectedNode._children !== 'undefined') {
                    if (typeof selectedNode.children !== 'undefined') {
                        selectedNode.children.push(draggingNode);
                    } else {
                        selectedNode._children.push(draggingNode);
                    }
                } else {
                    selectedNode.children = [];
                    selectedNode.children.push(draggingNode);
                }

                expand(selectedNode);
                endDrag();
            } else {
                endDrag();
            }
        });

    function endDrag() {
        selectedNode = null;
        d3.selectAll('.ghostCircle').attr('class', 'ghostCircle');
        d3.select(domNode).attr('class', 'node');
        // now restore the mouseover event or we won't be able to drag a 2nd time
        d3.select(domNode).select('.ghostCircle').attr('pointer-events', '');
        updateTempConnector();
        if (draggingNode !== null) {
            update(root);
            draggingNode = null;
        }
    }

    function collapse(d) {
        if (d.children) {
            d._children = d.children;
            d._children.forEach(collapse);
            d.children = null;
        }
    }

    function expand(d) {
        if (d._children) {
            d.children = d._children;
            d.children.forEach(expand);
            d._children = null;
        }
    }

    var overCircle = function (d) {
        selectedNode = d;
        updateTempConnector();
    };
    var outCircle = function (d) {
        selectedNode = null;
        updateTempConnector();
    };

    // Function to update the temporary connector indicating dragging affiliation
    var updateTempConnector = function () {
        var data = [];
        if (draggingNode !== null && selectedNode !== null) {
            // have to flip the source coordinates since we did this for the existing connectors on the original tree
            data = [
                {
                    source: {
                        x: selectedNode.y0,
                        y: selectedNode.x0
                    },
                    target: {
                        x: draggingNode.y0,
                        y: draggingNode.x0
                    }
                }
            ];
        }
        var link = svgGroup.selectAll(".templink").data(data);

        link.enter().append("path")
            .attr("class", "templink")
            .attr("d", d3.svg.diagonal())
            .attr('pointer-events', 'none');

        link.attr("d", d3.svg.diagonal());

        link.exit().remove();
    };

    // Toggle children function
    function toggleChildren(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else if (d._children) {
            d.children = d._children;
            d._children = null;
        }
        return d;
    }

    // Toggle children on click.
    function dblclick(d) {
        if (d3.event.defaultPrevented) return; // click suppressed
        d = toggleChildren(d);
        update(d);
    }

    function click(d) {
        d3.select(".selectedNode").attr("class", "node");
        if (clickedNode != d) {
            d3.select(this).attr("class", "selectedNode node");
            clickedNode = d;
            $("#toolbar").slideDown();
        }
        else {
            clickedNode = null;
            $("#toolbar").slideUp();
        }
    }


    function update(source) {
        // FIN TAILLE

        // Pour calculer automatiquement la hauteur de l'arborescence (ne fonctionne pas comme je veux)
        tree = tree.size([viewerHeight, viewerWidth]);


        var nodes = tree.nodes(root).reverse(), links = tree.links(nodes);
        // calcul de l'espacement entre les noeuds
        nodes.forEach(function (d) {
            //d.y = (d.depth * (maxLabelLength * 10)); //maxLabelLength * 10px
            d.y = d.depth * 180 + margin.left;
        });

        // FIN TAILLE //

        // Update the nodes…
        node = svgGroup.selectAll("g.node")
            .data(nodes, function (d) {
                return d.id;
            });

        // Enter any new nodes at the parent's previous position.
        var nodeEnter = node.enter().append("g")
            .call(dragListener)
            .attr("class", "node")
            .attr("transform", function (d) {
                return "translate(" + source.y0 + "," + source.x0 + ")";
            })
            .on('click', click)
            .on('dblclick', dblclick);

        nodeEnter.append("circle")
            .attr('class', 'nodeCircle')
            .attr("r", 0)
            .style("fill", function (d) {
                return d._children ? "lightsteelblue" : "#fff";
            });

        nodeEnter.append("text")
            .attr("x", function (d) {
                return d.children || d._children ? -10 : 10;
            })
            .attr("dy", ".35em")
            .attr('class', 'nodeText')
            .attr("text-anchor", function (d) {
                return d.children || d._children ? "end" : "start";
            })
            .text(function (d) {
                return d.name;
            })
            .style("fill-opacity", 0);

        // phantom node to give us mouseover in a radius around it
        nodeEnter.append("circle")
            .attr('class', 'ghostCircle')
            .attr("r", 50)
            .attr("opacity", 0.1)
            .style("fill", "green")
            .attr('pointer-events', 'mouseover')
            .on("mouseover", function (node) {
                overCircle(node);
            })
            .on("mouseout", function (node) {
                outCircle(node);
            });

        // Création / MAJ du texte, il change de position et gras si le noeud a des enfants
        node.select('text')
            .attr("x", function (d) {
                return d.children || d._children ? -10 : 10;
            })
            .attr("text-anchor", function (d) {
                return d.children || d._children ? "end" : "start";
            })
            .style("font-weight", function (d) {
                return d._children ? "bold" : "normal";
            })
            .text(function (d) {
                return d.name;
            });

        node.select("circle.nodeCircle")
            .attr("r", 7)
            .style("fill", function (d) {
                if(d.parent) {
                    d.color = d.color || d.parent.color || "white";
                }
                else {
                    d.color = d.color || "white";
                }
                return d.color;
            });

        // Transition nodes to their new position.
        var nodeUpdate = node.transition()
            .duration(duration)
            .attr("transform", function (d) {
                return "translate(" + d.y + "," + d.x + ")";
            });

        // Fade the text in
        nodeUpdate.select("text")
            .style("fill-opacity", 1);

        // Transition exiting nodes to the parent's new position.
        var nodeExit = node.exit().transition()
            .duration(duration)
            .attr("transform", function (d) {
                return "translate(" + source.y + "," + source.x + ")";
            })
            .remove();

        nodeExit.select("circle")
            .attr("r", 0);

        nodeExit.select("text")
            .style("fill-opacity", 0);

        // Update the links…
        var link = svgGroup.selectAll("path.link")
            .data(links, function (d) {
                return d.target.id;
            });

        // Enter any new links at the parent's previous position.
        link.enter().insert("path", "g")
            .attr("class", "link")
            .attr("d", function (d) {
                var o = {
                    x: source.x0,
                    y: source.y0
                };
                return diagonal({
                    source: o,
                    target: o
                });
            });

        // Transition links to their new position.
        link.transition()
            .duration(duration)
            .attr("d", diagonal);

        // Transition exiting nodes to the parent's new position.
        link.exit().transition()
            .duration(duration)
            .attr("d", function (d) {
                var o = {
                    x: source.x,
                    y: source.y
                };
                return diagonal({
                    source: o,
                    target: o
                });
            })
            .remove();

        // Stash the old positions for transition.
        nodes.forEach(function (d) {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }


    function getDataFromNodes(node) {
        var jzon = new Object();
        jzon.name = node.name;
        jzon.id = node.id;
        jzon.color = node.color || "";
        if(node.children) {
            jzon.children = new Array();
            node.children.forEach(function (d) {
                child = getDataFromNodes(d);
                jzon.children.push(child);
            });
        }
        return jzon;
    }

    // Append a group which holds all nodes and which the zoom Listener can act upon
    var svgGroup = baseSvg.append("g");

    // Layout the tree initially and center on the root node.
    if(jQuery.isEmptyObject(treeData) == false) {
        root = treeData;
        root.x0 = viewerHeight / 2;
        root.y0 = margin.left;
        update(root);
        $("#toolbar").slideUp();
    }
    else {
        $("#toolbar").slideDown();
    }
});