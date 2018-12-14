/**
 * data-tip 提示样式
 * @Author   ZhaoXianFang
 * @DateTime 2018-12-06
 */
;
(function($, window, document, undefined) {
    var modePos;
    $.fn.tip = function(options) {
        var set = $.extend({
            "mode": "bottom",
            "speed": 300,
            "tipText": "暂无提示内容"
        }, options);
        if (!modePos) {
            //策略模式
            //算法
            modePos = {
                top: function(t, tip) {
                    return {
                        left: t.offset().left + (t.width() - tip.width()) / 2 + "px",
                        top: t.offset().top - tip.height() - 12 + "px"
                    }
                },
                bottom: function(t, tip) {
                    return {
                        left: this.top(t, tip).left,
                        top: t.offset().top + t.height() + 12 + "px"
                    }
                },
                left: function(t, tip) {
                    return {
                        left: t.offset().left - tip.width() - 12 + "px",
                        top: t.offset().top + (t.height() - tip.height()) / 2 + "px"
                    }
                },
                right: function(t, tip) {
                    return {
                        left: t.offset().left + t.width() + 12 + "px",
                        top: t.offset().top + (t.height() - tip.height()) / 2 + "px"
                    }
                }
            };
        }

        function Tip(_this) {
            var _that = $(_this);
            var _mode = set.mode;
            var tipText = set.tipText;
            var _tip = ".tip-container";
            if (_that.data("mode")) {
                _mode = _that.data("mode");
            }
            if (_that.data("tip")) {
                tipText = _that.data("tip");
            }
            _that.css("cursor", "pointer");
            _that.hover(function() {
                var _tipHtml = '<div class="tip-container"><div class="tip-point-' + _mode + '"><div class="tip-content">' + tipText + '</div></div></div>';
                _that.removeAttr("title alt");
                $("body").append(_tipHtml);
                $(_tip).css(modePos[_mode](_that, $(_tip))).fadeIn(set.speed);
            }, function() {
                $(".tip-container").remove();
            });
        }
        return this.each(function() {
            return new Tip(this);
        });
    }
})(jQuery, window, document);
$("[data-tip]").tip();