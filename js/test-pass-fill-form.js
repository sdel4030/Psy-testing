/**
 * Optimized & Modernized Core Quiz Engine for WordPress 7+
 * Cleansed from deprecated Webshim/Evercookie & fixed .prop() bugs.
 */

var Wpt = Wpt || {};
Wpt.form = Wpt.form || {};

// حذف نیازمندی به Webshim؛ چرا که مرورگرهای مدرن به طور بومی از HTML5 Validation پشتیبانی می‌کنند.
Wpt.initWebshim = function(baseUrl) {
    if (this.initialized) return;
    this.initialized = true;
    console.log('Wpt: Using native browser form validation.');
};

if (Wpt.webshimBaseurl) {
    Wpt.initWebshim(Wpt.webshimBaseurl);
}

jQuery(document).ready(function($) {
    Wpt.initWebshim(Wpt.webshimBaseurl);
    Wpt.initDeviceIdentifier(); // جایگزین بهینه برای evercookie

    $('.wpt_test_form').each(function(i, formEl) {
        var form = $(formEl);

        Wpt.form.initQuestionAnswered(form);
        Wpt.form.setupSubmitDisable(form);
        Wpt.form.setupResetAnswers(form);
        Wpt.form.setupProgressMeter($, form);
        Wpt.form.setupQuestionsAnswered($, form);
    });
});

Wpt.form.initQuestionAnswered = function(form) {
    form.on('question_answered_initially.wpt', function(event, question) {
        question.addClass('answered');
        question.find('.answer input:first').removeAttr('required').removeAttr('aria-required');
    }).on('question_unanswered_initially.wpt', function(event, question) {
        question.removeClass('answered');
        question.find('.answer input:first').attr({ 'required': 'required', 'aria-required': 'true' });
    }).on('answer_selected.wpt', function (event, answer) {
        answer.addClass('selected');
    }).on('answer_unselected.wpt', function (event, answer) {
        answer.removeClass('selected');
    });
};

// پیاده‌سازی شناسه یکتا برای کاربر با استفاده از متد استاندارد و پرسرعت LocalStorage
Wpt.initDeviceIdentifier = function() {
    try {
        if (!localStorage.getItem('wpt_device_uuid')) {
            // ایجاد یک UUID ساده و تصادفی در صورت عدم وجود ساختار پیچیده uuid.v4
            var uuidStr = (typeof uuid !== 'undefined') ? uuid.v4() : 'wpt-' + Math.random().toString(36).substring(2, 15);
            localStorage.setItem('wpt_device_uuid', uuidStr);
        }
    } catch (e) {
        console.warn('LocalStorage is disabled or not supported.');
    }
};

Wpt.form.setupSubmitDisable = function(form) {
    var button = form.find('.button');

    form.on('test_filled.wpt', function() {
        button.removeClass('disabled').prop('disabled', false);
    }).on('test_unfilled.wpt', function() {
        button.addClass('disabled').prop('disabled', true);
    });
};

Wpt.form.setupResetAnswers = function(form) {
    var settings = form.data('settings');
    if (!settings || !settings.isResetAnswersOnBack) {
        return;
    }
    form.on('init_answers.wpt', function(event, answersInputs) {
        answersInputs.prop('checked', false); // اصلاح اصولی با prop
    });
};

Wpt.form.setupProgressMeter = function($, form) {
    var settings = form.data('settings');
    if (!settings || !settings.isShowProgressMeter) {
        return;
    }

    var initialTitle = document.title,
        separator    = Wpt.titleSeparator || ' | ',
        template     = Wpt.percentsAnswered || '{percentage}%';

    $(document).on('percentage_change.wpt', function(event, percent) {
        document.title = template.replace('{percentage}', percent) + ' ' + separator + ' ' + initialTitle;
    });
};

Wpt.form.setupQuestionsAnswered = function($, form) {
    var questionData = form.data('questions') || { answered: 0, total: 0 };
    var questionsAnswered  = questionData.answered,
        questions          = form.find('.question'),
        questionsMinFilled = questionsAnswered + questions.length,
        questionsTotal     = questionData.total;

    var answersInputs = form.find('input:radio, input:checkbox');
    form.trigger('init_answers.wpt', [answersInputs]).trigger('test_unfilled.wpt');

    // بهینه‌سازی تابع جایگزینی پلیس‌هولدر متن سوالات
    function replacePlaceholdersIn(el) {
        var RE_PLACEHOLDER = /(_{2,})/g;
        el.add(el.children()).contents().each(function() {
            if (this.nodeType === 3 && RE_PLACEHOLDER.test($(this).text())) {
                $(this).replaceWith($(this).text().replace(RE_PLACEHOLDER, '<span class="placeholder">$1</span>'));
            }
        });
        return el;
    }

    form.find('.question').each(function () {
        var question = $(this),
            title    = question.find('.title .title');

        replacePlaceholdersIn(title);
        var placeholder = title.find('.placeholder');

        question.data('isAnswered', false);
        var questionAnswersInputs = question.find('.answer input');

        question.find('.answer').each(function () {
            var answer = $(this);
            
            answer.find('input').on('change', function () {
                // اصلاح کلیدی: جایگزینی .attr() با .prop() جهت کارکرد صحیح در وردپرس جدید
                var isChecked = $(this).prop('checked');
                answer.data('isSelected', isChecked);

                if (isChecked) {
                    form.trigger('answer_selected.wpt', [answer]);
                    
                    // اگر دکمه رادیویی است، بقیه گزینه‌های این سوال را از حالت انتخاب کلاس خارج کن
                    if ($(this).is(':radio')) {
                        question.find('.answer').not(answer).each(function() {
                            var $otherAnswer = $(this);
                            if ($otherAnswer.data('isSelected')) {
                                $otherAnswer.data('isSelected', false);
                                form.trigger('answer_unselected.wpt', [$otherAnswer]);
                            }
                        });
                    }
                } else {
                    form.trigger('answer_unselected.wpt', [answer]);
                }

                var currentCheckedCount = questionAnswersInputs.filter(':checked').length;

                if (currentCheckedCount === 0 && question.data('isAnswered')) {
                    question.data('isAnswered', false);
                    questionsAnswered--;
                    form.trigger('question_unanswered_initially.wpt', [question, questionsAnswered, questionsTotal, questionsMinFilled]);
                    form.trigger('question_unanswered.wpt', [question, answer, placeholder]);
                } else if (currentCheckedCount > 0 && !question.data('isAnswered')) {
                    question.data('isAnswered', true);
                    questionsAnswered++;
                    form.trigger('question_answered_initially.wpt', [question, questionsAnswered, questionsTotal, questionsMinFilled]);
                    form.trigger('question_answered.wpt', [question, answer, placeholder]);
                }
            });
        });
    });

    function calculateAnswersPercentage(event, question, answered, total, minFilled) {
        var percent = total > 0 ? Math.round(100 * (answered / total)) : 0;
        $(document).trigger('percentage_change.wpt', [percent]);
        if (answered >= minFilled) {
            form.trigger('test_filled.wpt');
        } else {
            form.trigger('test_unfilled.wpt');
        }
    }

    form.on('question_answered_initially.wpt',   calculateAnswersPercentage)
        .on('question_unanswered_initially.wpt', calculateAnswersPercentage);

    if (questionsAnswered > 0) {
        calculateAnswersPercentage({}, form.find('.question:first'), questionsAnswered, questionsTotal, questionsMinFilled);
    }
    
    // اجرای تغییر اولیه بر اساس دکمه‌های از قبل پر شده به روش استاندارد
    answersInputs.filter(':checked').trigger('change');
};
