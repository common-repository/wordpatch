!function (e) {
    var t, a = e();
    e.fn.h5sortable = function (n) {
        var r = String(n);
        return n = e.extend({connectWith: !1}, n), this.each(function () {
            var i = e(this);
            if (/^enable|disable|destroy$/.test(r)) {
                var d = e(this).children(e(this).data("items")).attr("draggable", "enable" == r);
                return void("destroy" == r && d.add(this).removeData("connectWith items").off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s"))
            }
            var o = function (e) {
                i.data("sortableIsHandle", e)
            }, s = function () {
                return "undefined" == typeof i.data("sortableIsHandle") ? !1 : i.data("sortableIsHandle")
            }, l = function (e) {
                i.data("sortableIndex", e)
            }, h = function () {
                return i.data("sortableIndex")
            }, c = function () {
                return i.children(n.items).not(".sortable-placeholder")
            };
            n.items = "undefined" == typeof n.items ? "" : n.items;
            var f = "undefined" != typeof n.onBuildPlaceholder ? n.onBuildPlaceholder : function () {
            }, u = "undefined" != typeof n.onUpdateSortOrder ? n.onUpdateSortOrder : function () {
            }, g = c();
            if (g.attr("draggable", "true"), !i.data("sortableInitialized")) {
                i.data("sortableInitialized", !0);
                var m = e("<" + (/^ul|ol$/i.test(this.tagName) ? "li" : /^tbody|table$/i.test(this.tagName) ? "tr" : "div") + ' class="sortable-placeholder">');
                i.delegate(n.items + " " + n.handle, "mousedown", function () {
                    o(!0)
                }).delegate(n.items + " " + n.handle, "mouseup", function () {
                    o(!1)
                }), e(this).data("items", n.items), a = a.add(m), n.connectWith && e(n.connectWith).add(this).data("connectWith", n.connectWith), i.delegate(n.items, "dragstart.h5s", function (a) {
                    if (n.handle && !s()) return !1;
                    o(!1);
                    var r = a.originalEvent.dataTransfer;
                    r.effectAllowed = "move", r.setData("Text", "dummy"), t = e(this), t.addClass("sortable-dragging"), f(m, t), l(t.index())
                }).delegate(n.items, "dragend.h5s", function () {
                    var e = c();
                    t.removeClass("sortable-dragging").show(), a.detach(), h() != t.index() && (e.parent().trigger("sortupdate", {item: t}), u()), t = null
                }).delegate(":not(a[href], img)", "selectstart.h5s", function () {
                    return this.dragDrop && this.dragDrop(), !1
                }), e("body").delegate(n.container + " " + n.items + ", " + n.container + ", " + n.container + " .sortable-placeholder", "dragover.h5s dragenter.h5s drop.h5s", function (r) {
                    var d = c();
                    return d.is(t) || n.connectWith === e(t).parent().data("connectWith") ? "drop" == r.type ? (r.stopPropagation(), a.filter(":visible").after(t), !1) : (r.preventDefault(), r.originalEvent.dataTransfer.dropEffect = "move", d.is(this) ? (n.forcePlaceholderSize && m.height(t.outerHeight()), t.hide(), e(this)[m.index() < e(this).index() ? "after" : "before"](m), a.not(m).detach()) : a.is(this) || i.children(n.items).length || (a.detach(), e(this).append(m)), !1) : !0
                })
            }
        })
    }
}(jQuery);