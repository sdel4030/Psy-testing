/**
 * Modernized & High-Performance Diagnostic Charting Engine for WordPress 7+
 * Refactored using ES6, Debounced Resize handler, and safe dynamic axis calculations.
 */

function WptLineDiagramOptions() {
    return this
        .setGoldenSizeRatio()
        .setGutter(32)
        .setDash('.')
        .setLineColor('#888')
        .showAxis('left')
        .showAxis('bottom')
        .setAxisColor('#999')
        .setAxisTextColor('#000')
        .setIsSmooth(true)
        .setSymbolColorOpacity(0.8)
        .setAnnotationRadius(6)
        .setAnnotationColor('#fff')
        .setAnnotationTextColor('#000');
}

WptLineDiagramOptions.prototype.sizeRatio = 1;
WptLineDiagramOptions.prototype.setSizeRatio = function(value) {
    this.sizeRatio = value;
    return this;
};

WptLineDiagramOptions.prototype.setGoldenSizeRatio = function() {
    const GOLDEN_RATIO = 1.618033988749895;
    return this.setSizeRatio(GOLDEN_RATIO);
};

WptLineDiagramOptions.prototype.gutter = 0;
WptLineDiagramOptions.prototype.setGutter = function(value) {
    this.gutter = value;
    return this;
};

WptLineDiagramOptions.prototype.dash = '';
WptLineDiagramOptions.prototype.setDash = function(value) {
    this.dash = value;
    return this;
};

WptLineDiagramOptions.prototype.lineColor = '#000';
WptLineDiagramOptions.prototype.setLineColor = function(value) {
    this.lineColor = value;
    return this;
};

WptLineDiagramOptions.prototype.axis = '0 0 0 0';
WptLineDiagramOptions.prototype.textAxisIndex = null;
WptLineDiagramOptions.prototype.valueAxisIndex = null;

WptLineDiagramOptions.prototype.hideAxises = function() {
    this.textAxisIndex = null;
    this.valueAxisIndex = null;
    this.axis = '0 0 0 0';
    return this;
};

WptLineDiagramOptions.prototype.showAxis = function(name) {
    const map = { 'top': 0, 'right': 1, 'bottom': 2, 'left': 3 };
    const index = map[name];
    if (typeof index === 'undefined') return this;

    let axes = this.axis.split(' ');
    if (axes.length !== 4) {
        axes = [0, 0, 0, 0];
    }
    axes[index] = 1;
    this.axis = axes.join(' ');

    this.textAxisIndex = null;
    let axesIndex = -1;
    for (let i = 0; i < 4; i++) {
        if (parseInt(axes[i], 10) === 0) continue;
        axesIndex++;
        if (i === 0 || i === 2) {
            this.textAxisIndex = axesIndex;
        } else {
            this.valueAxisIndex = axesIndex;
        }
    }
    return this;
};

WptLineDiagramOptions.prototype.valueAxisTemplate = null;
WptLineDiagramOptions.prototype.setValueAxisTemplate = function(value) {
    this.valueAxisTemplate = value;
    return this;
};

WptLineDiagramOptions.prototype.isSmooth = false;
WptLineDiagramOptions.prototype.setIsSmooth = function(value) {
    this.isSmooth = value;
    return this;
};

WptLineDiagramOptions.prototype.symbolColorOpacity = 1;
WptLineDiagramOptions.prototype.setSymbolColorOpacity = function(value) {
    this.symbolColorOpacity = value;
    return this;
};

WptLineDiagramOptions.prototype.annotationRadius = 5;
WptLineDiagramOptions.prototype.setAnnotationRadius = function(value) {
    this.annotationRadius = value;
    return this;
};

WptLineDiagramOptions.prototype.annotationColor = '';
WptLineDiagramOptions.prototype.setAnnotationColor = function(value) {
    this.annotationColor = value;
    return this;
};

WptLineDiagramOptions.prototype.annotationTextColor = '';
WptLineDiagramOptions.prototype.setAnnotationTextColor = function(value) {
    this.annotationTextColor = value;
    return this;
};

WptLineDiagramOptions.prototype.axisColor = '#000';
WptLineDiagramOptions.prototype.setAxisColor = function(value) {
    this.axisColor = value;
    return this;
};

WptLineDiagramOptions.prototype.axisTextColor = '#000';
WptLineDiagramOptions.prototype.setAxisTextColor = function(value) {
    this.axisTextColor = value;
    return this;
};

// ==========================================
// CORE GRAPH RENDERER
// ==========================================

function WptLineDiagram(wrapper, holder, data, $, options) {
    this
        .setupOptions(options)
        .setupPaper(wrapper, holder, $)
        .setupData(data)
        .createDiagram(holder, $)
        .addTextLabels()
        .addValueLabels();
}

WptLineDiagram.prototype.options = new WptLineDiagramOptions();

WptLineDiagram.prototype.setupOptions = function(options) {
    if (options) this.options = options;
    return this;
};

WptLineDiagram.prototype.setupPaper = function(wrapper, holder, $) {
    const $wrapper = $(wrapper),
          $holder  = $(holder);
    let width = $holder.width();

    $wrapper.height(this.calculateHeight($wrapper.width()));
    $holder.height(this.calculateHeight(width));

    this.paper = new ScaleRaphael($holder.attr('id'), width, $holder.height());

    let lastWidth = 0;
    let resizeTimeout;
    const self = this;

    // بهینه‌سازی کلیدی: پیاده‌سازی مکانیزم دبانس برای جلوگیری از لگ در زمان ویرایش اندازه مرورگر
    $(window).on('resize.wptDiagram', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const currentWidth = $wrapper.width();
            if (currentWidth === lastWidth) return;
            
            lastWidth = currentWidth;
            $wrapper.height(self.calculateHeight(lastWidth));
            self.paper.changeSize(currentWidth, $wrapper.height());
        }, 150); // تاخیر ۱۵۰ میلی‌ثانیه‌ای برای پردازش بهینه و نرم
    });

    return this;
};

WptLineDiagram.prototype.setupData = function(data) {
    const isSingle = data.length === 1;
    this.data = data;
    
    if (isSingle) {
        const empty = { value: data[0].value, title: '', minimum: 0, maximum: 0 };
        this.data.unshift(empty);
        this.data.push(empty);
    }

    this.dataX = [];
    this.dataY = [];
    this.dataMinimum = 0;
    this.dataMaximum = 0;

    const iMax = this.data.length - 1;
    for (let i = 0; i <= iMax; i++) {
        if (i === 0) {
            this.data[i].hoverDir = 'right';
        } else if (i === iMax) {
            this.data[i].hoverDir = 'left';
        } else if (this.data[i].ratio <= 0.5) {
            this.data[i].hoverDir = 'up';
        } else {
            this.data[i].hoverDir = 'down';
        }

        this.dataX.push(i);
        this.dataY.push(this.data[i].value);

        this.dataMinimum = Math.min(this.dataMinimum, this.data[i].minimum || 0);
        this.dataMaximum = Math.max(this.dataMaximum, this.data[i].maximum || 0);
    }

    return this;
};

WptLineDiagram.prototype.createDiagram = function(holder, $) {
    const self = this;
    let axisYStep = this.dataMaximum - this.dataMinimum;
    
    while (axisYStep > 20) {
        axisYStep = Math.round(axisYStep / 10);
    }

    this.diagram = this.paper.linechart(
        0, 0, $(holder).width(), $(holder).height(),
        [this.dataX, [0, 0]], [this.dataY, [this.dataMinimum, this.dataMaximum]],
        {
            gutter      : this.options.gutter,
            dash        : this.options.dash,
            colors      : [this.options.lineColor],
            symbol      : 'circle',
            shade       : true,
            axisxstep   : this.dataX.length - 1,
            axisystep   : axisYStep,
            axis        : this.options.axis,
            smooth      : this.options.isSmooth
        }
    );

    // حذف نمادهای خط فرضی دوم برای تمیزی چارت
    if (this.diagram.symbols && this.diagram.symbols[1]) {
        this.diagram.symbols[1].remove();
    }

    // رنگ‌آمیزی بر اساس مقادیر واقعی مقیاس‌ها
    this.diagram.eachColumn(function() {
        if ('' === self.data[this.axis].title) {
            this.symbols.hide();
        } else {
            this.symbols.attr({ fill: Raphael.getColor(self.options.symbolColorOpacity) });
        }
    });

    // بهینه‌سازی فرآیند پاپ‌آپ‌های روی نمودار (Hover Annotations)
    const hoverAttr = [
        { fill: self.options.annotationTextColor },
        { fill: self.options.annotationColor }
    ];

    this.diagram.hoverColumn(function () {
        const data = self.data[this.axis];
        if (!data || '' === data.title) return;
        
        if (data.abbr) data.abbr.hide();
        
        const text = $.grep([
            data.ratioTitle,
            data.title.replace(/\s+/g, '\n').replace(/(\/)/g, '$1\n'),
            data.valueTitle
        ], Boolean).join('\n');

        this.hoverPopup = self.paper
            .popup(this.x, this.y[0], text, data.hoverDir)
            .attr(hoverAttr)
            .insertBefore(this);
    }, function () {
        const data = self.data[this.axis];
        if (!data || '' === data.title) return;
        
        if (this.hoverPopup) this.hoverPopup.remove();
        if (data.abbr) data.abbr.show();
    });

    // تنظیم استایل‌های ظاهری محورها با ساختار امن و مدرن
    this.diagram.axis.forEach(function(axis) {
        if (axis && axis.attr) {
            axis.attr({ stroke: self.options.axisColor });
            if (axis.text) axis.text.attr({ fill: self.options.axisTextColor });
        }
    });

    return this;
};

WptLineDiagram.prototype.addTextLabels = function() {
    if (null === this.options.textAxisIndex) return this;

    const data      = this.data,
          axis      = this.diagram.axis[this.options.textAxisIndex],
          texts     = axis.text,
          lastIndex = texts.length - 1,
          maxWidth  = axis.paper.width / lastIndex - 5,
          eMaxWidth = maxWidth / 2 + this.options.gutter * 0.6,
          self      = this;

    const abbrAngle  = 45,
          abbrRadius = this.options.annotationRadius * 0.6,
          abbrAttr   = [
              { fill: this.options.annotationColor, stroke: this.options.lineColor },
              { fill: this.options.annotationTextColor }
          ];

    this.diagram.eachColumn(function() {
        if ('' === self.data[this.axis].title) return;
        const text = self.data[this.axis].abbrTitle;
        self.data[this.axis].abbr = self.paper
            .tag(this.x, this.y[0], text, abbrAngle, abbrRadius)
            .attr(abbrAttr)
            .insertBefore(this.symbols);
    });

    texts.forEach(function(text, index) {
        let iMaxWidth = maxWidth;

        if (lastIndex > 0 && (index === 0 || index === lastIndex)) {
            iMaxWidth = eMaxWidth;
            text
                .attr('text-anchor', (index === 0) ? 'start' : 'end')
                .transform('t' + (self.options.gutter * ((index === 0) ? -0.5 : 0.5)) + ',0');
        }

        setTextToFit(text, data[index].title, iMaxWidth, ' ');
    });

    function setTextToFit(el, text, maxWidth, splitBy) {
        const words = text.split(splitBy);
        let add = '';
        for (let i = words.length - 1; i >= 0; i--) {
            const current = words.join(splitBy) + add;
            el.attr('text', current);
            if (maxWidth > el.getBBox().width) return;
            if (i === 0 && splitBy !== '') {
                setTextToFit(el, current, maxWidth, '');
                return;
            }
            add = '...';
            words.splice(-1, 1);
        }
    }

    return this;
};

WptLineDiagram.prototype.addValueLabels = function() {
    if (null === this.options.valueAxisIndex || null === this.options.valueAxisTemplate) {
        return this;
    }

    const axis = this.diagram.axis[this.options.valueAxisIndex];
    const template = this.options.valueAxisTemplate;

    // تصحیح اسکوپ اجرایی برای الحاق رشته الگو به برچسب‌های عمودی چارت
    axis.text.forEach(function(text) {
        if (text && text.attr) {
            text.attr('text', template.replace('{value}', text.attr('text')));
        }
    });

    return this;
};

WptLineDiagram.prototype.calculateHeight = function(width) {
    return Math.round(width / this.options.sizeRatio);
};

WptLineDiagram.prototype.calculateWidth = function(height) {
    return Math.round(height * this.options.sizeRatio);
};
