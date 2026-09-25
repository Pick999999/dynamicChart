/**
 * AimaraJS - Pure JavaScript TreeView Component
 * Enhanced version for Vision UI Dashboard
 * Based on Rafael Thca's AimaraJS (https://www.cssscript.com/pure-javascript-treeview-component-aimarajs/)
 */

// Global helper functions required by AimaraJS
function createSimpleElement(type, id, class_name) {
  var element = document.createElement(type);
  if (id != undefined && id !== null) element.id = id;
  if (class_name != undefined && class_name !== null) element.className = class_name;
  return element;
}

function createImgElement(id, class_name, src) {
  var element = document.createElement('img');
  if (id != undefined && id !== null) element.id = id;
  if (class_name != undefined && class_name !== null) element.className = class_name;
  if (src != undefined && src !== null) element.src = src;
  return element;
}

// Built-in SVG Icon Data URIs
var AIMARA_ICONS = {
  expand: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230075ff'%3E%3Cpath d='M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z'/%3E%3C/svg%3E",
  collapse: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230075ff'%3E%3Cpath d='M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z'/%3E%3C/svg%3E",
  server: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2338ef7d'%3E%3Cpath d='M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z'/%3E%3C/svg%3E",
  round: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2300c6ff'%3E%3Cpath d='M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z'/%3E%3C/svg%3E",
  asset: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ff9a44'%3E%3Cpath d='M5 9.2h3V19H5zM10.5 5h3v14h-3zM16 12.5h3V19h-3zM3 3h18v2H3z'/%3E%3C/svg%3E"
};

/**
 * Creating the tree component
 * @param {string} p_div - ID of the div where the tree will be rendered
 * @param {string} p_backColor - Background color of the region
 * @param {object} p_contextMenu - Object containing context menus
 */
function createTree(p_div, p_backColor, p_contextMenu) {
  var tree = {
    name: 'tree_' + Math.floor(Math.random() * 10000),
    div: p_div,
    ulElement: null,
    childNodes: [],
    backcolor: p_backColor || 'transparent',
    contextMenu: p_contextMenu || null,
    selectedNode: null,
    nodeCounter: 0,
    contextMenuDiv: null,
    rendered: false,
    iconExpand: AIMARA_ICONS.expand,
    iconCollapse: AIMARA_ICONS.collapse,

    // Custom Event Hooks
    nodeBeforeOpenEvent: null,
    nodeAfterOpenEvent: null,
    nodeBeforeCloseEvent: null,
    nodeAfterCloseEvent: null,
    nodeClickEvent: null,

    /**
     * Creating a new node
     * @param {string} p_text - Text displayed on the node
     * @param {boolean} p_expanded - Whether the node starts expanded
     * @param {string} p_icon - Relative path or Data URI to icon
     * @param {object} p_parentNode - Reference to parent node or null
     * @param {any} p_tag - Custom metadata attached to node
     * @param {string} p_contextmenu - Context menu key
     */
    createNode: function(p_text, p_expanded, p_icon, p_parentNode, p_tag, p_contextmenu) {
      var v_tree = this;
      var node = {
        id: 'node_' + this.nodeCounter,
        text: p_text,
        icon: p_icon,
        parent: p_parentNode,
        expanded: !!p_expanded,
        childNodes: [],
        tag: p_tag,
        contextMenu: p_contextmenu,
        elementLi: null,

        removeNode: function() { v_tree.removeNode(this); },
        toggleNode: function(p_event) { v_tree.toggleNode(this); },
        expandNode: function(p_event) { v_tree.expandNode(this); },
        expandSubtree: function() { v_tree.expandSubtree(this); },
        setText: function(p_text) { v_tree.setText(this, p_text); },
        collapseNode: function() { v_tree.collapseNode(this); },
        collapseSubtree: function() { v_tree.collapseSubtree(this); },
        removeChildNodes: function() { v_tree.removeChildNodes(this); },
        createChildNode: function(p_text, p_expanded, p_icon, p_tag, p_contextmenu) {
          return v_tree.createNode(p_text, p_expanded, p_icon, this, p_tag, p_contextmenu);
        }
      };

      this.nodeCounter++;

      if (this.rendered) {
        if (p_parentNode == undefined || p_parentNode === null) {
          this.drawNode(this.ulElement, node);
          this.adjustLines(this.ulElement, false);
        } else {
          var v_ul = p_parentNode.elementLi.getElementsByTagName("ul")[0];
          if (p_parentNode.childNodes.length == 0) {
            var v_img = p_parentNode.elementLi.getElementsByTagName("img")[0];
            v_img.style.visibility = "visible";
            if (p_parentNode.expanded) {
              v_ul.style.display = 'block';
              v_img.src = v_tree.iconCollapse;
              v_img.id = 'toggle_off';
            } else {
              v_ul.style.display = 'none';
              v_img.src = v_tree.iconExpand;
              v_img.id = 'toggle_on';
            }
          }
          this.drawNode(v_ul, node);
          this.adjustLines(v_ul, false);
        }
      }

      if (p_parentNode == undefined || p_parentNode === null) {
        this.childNodes.push(node);
        node.parent = this;
      } else {
        p_parentNode.childNodes.push(node);
      }

      return node;
    },

    /**
     * Render the tree
     */
    drawTree: function() {
      this.rendered = true;
      var div_tree = document.getElementById(this.div);
      if (!div_tree) return;
      div_tree.innerHTML = '';

      var ulElement = createSimpleElement('ul', this.name, 'tree');
      this.ulElement = ulElement;

      for (var i = 0; i < this.childNodes.length; i++) {
        this.drawNode(ulElement, this.childNodes[i]);
      }

      div_tree.appendChild(ulElement);
      this.adjustLines(document.getElementById(this.name), true);
    },

    /**
     * Draw individual node into UL container
     */
    drawNode: function(p_ulElement, p_node) {
      var v_tree = this;
      var v_icon = null;

      if (p_node.icon != null) {
        v_icon = createImgElement(null, 'icon_tree', p_node.icon);
      }

      var v_li = document.createElement('li');
      p_node.elementLi = v_li;

      var v_span = createSimpleElement('span', null, 'node');
      var v_exp_col = null;

      if (p_node.childNodes.length == 0) {
        v_exp_col = createImgElement('toggle_off', 'exp_col', this.iconCollapse);
        v_exp_col.style.visibility = "hidden";
      } else {
        if (p_node.expanded) {
          v_exp_col = createImgElement('toggle_off', 'exp_col', this.iconCollapse);
        } else {
          v_exp_col = createImgElement('toggle_on', 'exp_col', this.iconExpand);
        }
      }

      v_span.ondblclick = function() {
        v_tree.doubleClickNode(p_node);
      };

      v_exp_col.onclick = function(e) {
        e.stopPropagation();
        v_tree.toggleNode(p_node);
      };

      v_span.onclick = function(e) {
        v_tree.selectNode(p_node);
        if (typeof v_tree.nodeClickEvent === 'function') {
          v_tree.nodeClickEvent(p_node);
        }
      };

      v_span.oncontextmenu = function(e) {
        v_tree.selectNode(p_node);
        v_tree.nodeContextMenu(e, p_node);
      };

      if (v_icon != undefined && v_icon !== null) {
        v_span.appendChild(v_icon);
      }

      var v_a = createSimpleElement('a', null, null);
      v_a.innerHTML = p_node.text;
      v_span.appendChild(v_a);

      v_li.appendChild(v_exp_col);
      v_li.appendChild(v_span);

      p_ulElement.appendChild(v_li);

      var v_ul = createSimpleElement('ul', 'ul_' + p_node.id, null);
      v_li.appendChild(v_ul);

      if (p_node.childNodes.length > 0) {
        if (!p_node.expanded) {
          v_ul.style.display = 'none';
        }
        for (var i = 0; i < p_node.childNodes.length; i++) {
          this.drawNode(v_ul, p_node.childNodes[i]);
        }
      }
    },

    setText: function(p_node, p_text) {
      var span = p_node.elementLi.getElementsByTagName('span')[0];
      if (span && span.lastChild) {
        span.lastChild.innerHTML = p_text;
      }
      p_node.text = p_text;
    },

    expandNode: function(p_node) {
      if (p_node.childNodes.length > 0 && !p_node.expanded) {
        if (this.nodeBeforeOpenEvent != undefined) this.nodeBeforeOpenEvent(p_node);

        var img = p_node.elementLi.getElementsByTagName("img")[0];
        if (img) {
          img.src = this.iconCollapse;
          img.id = 'toggle_off';
        }
        var ul = p_node.elementLi.getElementsByTagName("ul")[0];
        if (ul) ul.style.display = 'block';

        p_node.expanded = true;
        if (this.nodeAfterOpenEvent != undefined) this.nodeAfterOpenEvent(p_node);
      }
    },

    collapseNode: function(p_node) {
      if (p_node.childNodes.length > 0 && p_node.expanded) {
        if (this.nodeBeforeCloseEvent != undefined) this.nodeBeforeCloseEvent(p_node);

        var img = p_node.elementLi.getElementsByTagName("img")[0];
        if (img) {
          img.src = this.iconExpand;
          img.id = 'toggle_on';
        }
        var ul = p_node.elementLi.getElementsByTagName("ul")[0];
        if (ul) ul.style.display = 'none';

        p_node.expanded = false;
        if (this.nodeAfterCloseEvent != undefined) this.nodeAfterCloseEvent(p_node);
      }
    },

    toggleNode: function(p_node) {
      if (p_node.expanded) {
        this.collapseNode(p_node);
      } else {
        this.expandNode(p_node);
      }
    },

    doubleClickNode: function(p_node) {
      this.toggleNode(p_node);
    },

    expandSubtree: function(p_node) {
      this.expandNode(p_node);
      for (var i = 0; i < p_node.childNodes.length; i++) {
        this.expandSubtree(p_node.childNodes[i]);
      }
    },

    collapseSubtree: function(p_node) {
      this.collapseNode(p_node);
      for (var i = 0; i < p_node.childNodes.length; i++) {
        this.collapseSubtree(p_node.childNodes[i]);
      }
    },

    expandAll: function() {
      for (var i = 0; i < this.childNodes.length; i++) {
        this.expandSubtree(this.childNodes[i]);
      }
    },

    collapseAll: function() {
      for (var i = 0; i < this.childNodes.length; i++) {
        this.collapseSubtree(this.childNodes[i]);
      }
    },

    selectNode: function(p_node) {
      var span = p_node.elementLi.getElementsByTagName("span")[0];
      if (this.selectedNode && this.selectedNode.elementLi) {
        var prevSpan = this.selectedNode.elementLi.getElementsByTagName("span")[0];
        if (prevSpan) prevSpan.className = 'node';
      }
      if (span) span.className = 'node_selected';
      this.selectedNode = p_node;
    },

    removeNode: function(p_node) {
      var index;
      if (p_node.parent == this) {
        index = this.childNodes.indexOf(p_node);
        this.childNodes.splice(index, 1);
      } else {
        index = p_node.parent.childNodes.indexOf(p_node);
        p_node.parent.childNodes.splice(index, 1);
      }
      if (p_node.elementLi && p_node.elementLi.parentNode) {
        p_node.elementLi.parentNode.removeChild(p_node.elementLi);
      }
      this.adjustLines(document.getElementById(this.name), true);
    },

    removeChildNodes: function(p_node) {
      while (p_node.childNodes.length > 0) {
        this.removeNode(p_node.childNodes[0]);
      }
    },

    nodeContextMenu: function(e, p_node) {
      if (e && e.preventDefault) e.preventDefault();
    },

    adjustLines: function(p_ul, p_recursive) {
      if (!p_ul) return;
      var treeNodes = p_ul.childNodes;
      for (var i = 0; i < treeNodes.length; i++) {
        var node = treeNodes[i];
        if (node.nodeType === 1 && node.tagName === 'LI') {
          if (i === treeNodes.length - 1) {
            node.className = (node.className ? node.className + ' ' : '') + 'last';
          }
          if (p_recursive) {
            var subUl = node.getElementsByTagName('ul')[0];
            if (subUl) this.adjustLines(subUl, true);
          }
        }
      }
    }
  };

  return tree;
}

window.createTree = createTree;
window.AIMARA_ICONS = AIMARA_ICONS;
