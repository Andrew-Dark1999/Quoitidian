<style type="text/css">
.bpm_store{
    float: left;
    min-height: 600px;
    position: relative;
    overflow: hidden;
    width: 100%;
}
.bpm_operator{  
    display: inline-block;
    border-radius: 5px;
    position: absolute;
    z-index: 2;
}
.bpm_buttons{
    display: none;
    position: absolute;
    top: 0;
    left: 65px;
    min-width: 50px;
}
.bpm_operator:hover .bpm_buttons{
    opacity: 0.5;
    display: block;
}
.bpm_operator:hover .bpm_buttons:hover{
    opacity: 1;
}
.bpm_buttons a{
    display: block;
    float: left;
    margin-right: 5px;
}
.bpm_user_name{
    position: absolute;
    left: 50%;
    top: 65px;
    width: 70px;
    margin-left: -35px;
    text-align: center;
}
.bpm_buttons{
    z-index: 10;
}
.bpm_def{
    display: inline-block;
}
.bpm_operator{
    position: static;
    width: 50px;
}
.bpm_operator .bpm_body{    
    width: 32px;
    height: 32px;
    border-radius: 20px;
    background: #c5c5c5;
    padding: 0;
    text-align: center;
    line-height: 32px;
    color: #fff;
    font-size: 14px;
    margin: 0 auto;
}
.bpm_operator i.circle:before{
    height: 18px;
    width: 18px;
    border-radius: 20px;
    border: 1px solid #fff;
    content: '';
    display: block;
    position: relative;
    top: 4px;
}
.bpm_operator i.diamond:before{
    height: 14px;
    width: 14px;
    border: 1px solid  #fff;
    -moz-transform: rotate(45deg); /* Для Firefox */
    -ms-transform: rotate(45deg); /* Для IE */
    -webkit-transform: rotate(45deg); /* Для Safari, Chrome, iOS */
    -o-transform: rotate(45deg); /* Для Opera */
    transform: rotate(45deg);
    content: '';
    display: block;
    position: relative;
    top: 2px;
}
.bpm_operator i{
    display: inline-block;
}
.bpm_operator.blue .bpm_body{
    background: #59ace2;
}
.bpm_operator.green .bpm_body{
    background: #1db4ab;
}
.bpm_operator.orange{
    background: none !important;
}
.bpm_operator.orange .bpm_body{
    background: #ff7c54;
}
.bpm_operator.violet .bpm_body{
    background: #a489d6;
}
.bpm_operator.yellow .bpm_body{
    background: #ffb71d;
}
.bpm_unit{
    background: #fff;
    margin: 15px 0;
    padding: 15px 10px;
    border-radius: 5px;
    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.2);
}
.bpm_uname{
    font-family: 'CustomOpenSans', sans-serif;
    font-size: 13px;
    font-weight: bold;
    color: #c3c3c3;
}
.bpm_uname+.bpm_tree {
    margin-top: 20px;
}
.bpm_tree .ui-state-highlight{
    display: inline-block;
    width: 160px;
}
.bpm_tree{
    padding-left: 15px;
}
.bpm_title{
    text-align: center;
    font-weight: bold;
}
svg.arrows{
    position:absolute;
    top:0;
    left:0;
}
.ui-draggable-dragging{
    opacity: 0.7;
}

</style>


    <svg class="arrows" width="2000" height="2000px">

    </svg>
<div class="bpm_top"> <!--клас bpm_top не принципиальный, может быть сменен на любой другой-->
    <button class="btn btn-create">Ответственный +</button>
    <button class="btn btn-default">Действия</button>
    <div class="bpm_def">
        <div class="bpm_operator blue">
            <div class="bpm_body">
                <i class="fa fa-flash"></i>
            </div>
        </div>
        <div class="bpm_operator green">
            <div class="bpm_body">
                <i class="fa fa-envelope-o"></i>
            </div>
        </div>
        <div class="bpm_operator orange">
            <div class="bpm_body">
                <i class="diamond"></i>
            </div>
        </div>
        <div class="bpm_operator violet">
            <div class="bpm_body">
                <i class="fa fa-tasks"></i>
            </div>
        </div>
        <div class="bpm_operator yellow">
            <div class="bpm_body">
                <i class="circle"></i>
            </div>
        </div>
    </div>
</div>
<div class="bpm_unit">
    <div class="bpm_uname">Клиент <i class="fa fa-cog"></i></div>
</div>
<div class="bpm_unit" style="height:250px;">
    <div class="bpm_uname">Сотрудник отдела продаж <i class="fa fa-cog"></i></div>
    <div class="bpm_tree">
        <div class="bpm_operator yellow begin" el-num="1">
            <div class="bpm_body">
                <i class="circle"></i>
            </div>
            <div class="bpm_title">Начало</div>
        </div>
        <div class="bpm_operator end" el-num="2">
            <div class="bpm_body">
                <i class="circle"></i>
            </div>
            <div class="bpm_title">Конец</div>
        </div>
        <div class="bpm_operator violet begin" el-num="3">
            <div class="bpm_body">
                <i class="fa fa-tasks"></i>
            </div>
            <div class="bpm_title">Задача</div>
        </div>
        <div class="bpm_operator green begin" el-num="4">
            <div class="bpm_body">
                <i class="fa fa-envelope-o"></i>
            </div>
            <div class="bpm_title">Сообщение</div>
        </div>
    </div>
</div>
<div class="bpm_unit" style="height:250px;">
    <div class="bpm_uname">Менеджер отдела продаж <i class="fa fa-cog"></i></div>
</div>
<div class="bpm_store hidden">
    <div class="bpm_operator user" style="left:0px; top:0px;">
        <svg width="53" height="53">
            <path fill="rgba(0,0,0,0)" stroke="#000000" d=" M0 52  h52  V35  c0 0 -5 -9 -16 -11  H17  C5 26 2e-14 36 2e-14 36  L0 52  z"/>
            <path fill="#000000" stroke="#000000" d=" M10 43  v9 "/>    
            <path fill="#000000" stroke="#000000" d=" M40 43  v9 "/>
            <circle fill="#000000" stroke="#000000" cx="26" cy="12" r="11.879"/>
            <path fill="#F1F0F1" stroke="#000000" d=" M15 14  c0 0 6.17 -5 12 -3.962  C33 12 37 9 37 9  c0 3 0 8 -3 12  c0 0 2 1.5 2 3  c0 1.5 0 4 -2 6.5  c-2.5 2.5 -12 2.5 -15 0  c-2 -2 -2 -4 -3 -6  c0 -2 1 -3 2.741 -4.202  C17 20 14 16 15 14  z"></path>
        </svg>
        <div class="bpm_user_name">User Name</div>
        <div class="bpm_buttons">
            <a href="#" class="roundrect">
                <svg width="18" height="18">
                    <rect x="1" y="1" rx="3" ry="3" width="15" height="15" style="fill:rgba(0,0,0,0);stroke:black;stroke-width:1;"></rect>
                </svg>
            </a>
            <a href="#" class="rect">
                <svg width="18" height="18">
                    <rect x="1" y="1" width="15" height="15" style="fill:rgba(0,0,0,0);stroke-width:1;stroke:rgb(0,0,0)" />
                </svg>
            </a>
            <a href="#" class="circle">
                <svg width="18" height="18">
                    <circle cx="9" cy="9" r="8" stroke="black" stroke-width="1" fill="rgba(0,0,0,0)"></circle>
                </svg>
            </a>
            <a href="#" class="computer">
                <svg width="18" height="18">                        
                    <rect x="3" y="1" fill="#FFFFFF" stroke="#000000" width="12" height="9"></rect>
                    <rect x="1" y="12" fill="#FFFFFF" stroke="#000000" width="16" height="5"></rect>                    
                </svg>
            </a>
        </div>
    </div>
    <div class="bpm_operator begin">
        <div class=""></div>
        <div class="bpm_title">Начало</div>
    </div>
    <div class="bpm_operator end">
        <div class=""></div>
        <div class="bpm_title">Конец</div>
    </div>
</div>
<div class="">

</div>

<div class="bpm_stock hidden">
    <div class="bpm_operator computer">
        <svg width="53" height="53">
            <rect x="16" y="11" fill="#FFFFFF" stroke="#000000" width="26" height="38"></rect>    
            <rect x="5" y="1" fill="#FFFFFF" stroke="#000000" width="44" height="34"></rect>    
            <rect x="9" y="5" fill="#FFFFFF" stroke="#000000" width="36" height="26"></rect>            
            <path fill="#ffffff" stroke="#000000" d=" M51 52  H1  v-14  h51  V53  z"></path>   
            <path fill="#ffffff" stroke="#000000" d=" M30 42 h20 "></path>
        </svg>
    </div>
    <div class="bpm_operator arrow">
        <svg width="80" height="20">
            <path class="arrow" d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path class="sting" d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>  
    <div class="bpm_operator circle">
        <svg width="53" height="53">
            <circle cx="26" cy="26" r="24" stroke="black" stroke-width="4" fill="rgba(0,0,0,0)"></circle>
        </svg>
    </div>
    <div class="bpm_operator rect">
        <svg width="53" height="53">
            <rect x="4" y="4" width="47" height="47" style="fill:rgba(0,0,0,0);stroke-width:4;stroke:rgb(0,0,0)"></rect>
        </svg>
    </div>
    <div class="bpm_operator roundrect">
        <svg width="53" height="53">
            <rect x="4" y="4" rx="10" ry="10" width="47" height="47" style="fill:rgba(0,0,0,0);stroke:black;stroke-width:4;" /></rect>
        </svg>
    </div>
</div>


<script>
var BPM = function() {};

BPM.arrowDraw = function() {  //первый запуск, структура не построена, только (начало) и (конец)
    var topDifB = $('div.bpm_operator.begin .bpm_body').offset().top - $('svg.arrows').offset().top;
    var leftDifB = $('div.bpm_operator.begin .bpm_body').offset().left - $('svg.arrows').offset().left;
    var centerYB = topDifB + $('div.bpm_operator.begin .bpm_body').height()/2;
    var centerXB = leftDifB + $('div.bpm_operator.begin .bpm_body').width();
    var topDifE = $('div.bpm_operator.end .bpm_body').offset().top - $('svg.arrows').offset().top;
    var leftDifE = $('div.bpm_operator.end .bpm_body').offset().left - $('svg.arrows').offset().left;
    var centerYE = topDifE + $('div.bpm_operator.end .bpm_body').height()/2;
    var centerXE = leftDifE-2;
    var stingX1 = centerXE-15;
    var stingY1 = centerYE-2.5;
    var stingX2 = centerXE-15;
    var stingY2 = centerYE+2.5;
    var numB = $('div.bpm_operator.begin').attr('el-num');
    var numE = $('div.bpm_operator.end').attr('el-num');
    var colorArr = $('div.bpm_operator.begin .bpm_body').css('background-color');    
    $('div.bpm_stock path.arrow').clone(true).appendTo('svg.arrows')
    .attr('d', 'M '+centerXB+' '+centerYB+' L '+centerXE+' '+centerYE+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+centerXE+' '+centerYE+'')
    .attr('stroke', colorArr+'').attr('arr-begin', numB+'').attr('arr-end', numE+'');
};

BPM.recount = function($this) {  //перерасчет стрелок при любом движении или добавлении елементов
    var elNumE = $this.attr('arr-end');
    var elNumB = $this.attr('arr-begin');
    var $elemBegin = $('div.bpm_operator[el-num="'+elNumB+'"] .bpm_body');
    var $elEnd = $('div.bpm_operator[el-num="'+elNumE+'"] .bpm_body');

    var widthB = $elemBegin.width();
    var heightB = $elemBegin.height();
    var widthE = $elEnd.width();
    var heightE = $elEnd.height();

    var topDifB = $elemBegin.offset().top - $('svg.arrows').offset().top;
    var leftDifB = $elemBegin.offset().left - $('svg.arrows').offset().left;
    var topDifE = $elEnd.offset().top - $('svg.arrows').offset().top;
    var leftDifE = $elEnd.offset().left - $('svg.arrows').offset().left;

    //p-point T-top L-left R-right B-bottom B-begin E-end x,y-coordinates ex:pBBy-(pont bottom begin y)
    //      pT
    //   pL( )pR
    //      pB  
    var pTBx = leftDifB + widthB / 2;
    var pTBy = topDifB;
    var pRBx = leftDifB + widthB;
    var pRBy = topDifB + heightB / 2;
    var pBBx = leftDifB + widthB / 2;
    var pBBy = topDifB + heightB + 36;
    var pLBx = leftDifB;
    var pLBy = topDifB + heightB / 2;

    var pTEx = leftDifE + widthE / 2;
    var pTEy = topDifE;
    var pREx = leftDifE + widthE;
    var pREy = topDifE + heightE / 2;
    var pBEx = leftDifE + widthE / 2;
    var pBEy = topDifE + heightE + 36;
    var pLEx = leftDifE;
    var pLEy = topDifE + heightE / 2;

    if (leftDifB+20 < leftDifE) {
        if (topDifB+20 < topDifE) {
            var stingX1 = pLEx-15;
            var stingY1 = pLEy-2.5;
            var stingX2 = pLEx-15;
            var stingY2 = pLEy+2.5;
            $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pBBx+' '+pLEy+' L '+pLEx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pLEx+' '+pLEy+'');
        } else if (topDifB-20 > topDifE) {
            var stingX1 = pBEx-2.5;
            var stingY1 = pBEy+15;
            var stingX2 = pBEx+2.5;
            var stingY2 = pBEy+15;
            $this.attr('d', 'M '+pRBx+' '+pRBy+' L '+pBEx+' '+pRBy+' L '+pBEx+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pBEx+' '+pBEy+'');
        } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
            var stingX1 = pLEx-15;
            var stingY1 = pLEy-2.5;
            var stingX2 = pLEx-15;
            var stingY2 = pLEy+2.5;
            $this.attr('d', 'M '+pRBx+' '+pRBy+' L '+pLEx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pLEx+' '+pLEy+'');
        }
    } else if (leftDifB-20 > leftDifE) {
        if (topDifB+20 < topDifE) {
            var stingX1 = pTEx-2.5;
            var stingY1 = pTEy-15;
            var stingX2 = pTEx+2.5;
            var stingY2 = pTEy-15;
            $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pBEx+' '+pLBy+' L '+pTEx+' '+pTEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pTEx+' '+pTEy+'');
        } else if (topDifB-20 > topDifE) {
            var stingX1 = pREx+15;
            var stingY1 = pREy-2.5;
            var stingX2 = pREx+15;
            var stingY2 = pREy+2.5;
            $this.attr('d', 'M '+pTBx+' '+pTBy+' L '+pTBx+' '+pREy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
        } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
            var stingX1 = pREx+15;
            var stingY1 = pREy-2.5;
            var stingX2 = pREx+15;
            var stingY2 = pREy+2.5;
            $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
        }
    } else {
        if (topDifB+20 < topDifE) {
            var stingX1 = pTEx-2.5;
            var stingY1 = pTEy-15;
            var stingX2 = pTEx+2.5;
            var stingY2 = pTEy-15;
            $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pTEx+' '+pTEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pTEx+' '+pTEy+'');
        } else if (topDifB-20 > topDifE) {
            var stingX1 = pBEx-2.5;
            var stingY1 = pBEy+15;
            var stingX2 = pBEx+2.5;
            var stingY2 = pBEy+15;
            $this.attr('d', 'M '+pTBx+' '+pTBy+' L '+pBEx+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pBEx+' '+pBEy+'');
        } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
            var stingX1 = pREx+15;
            var stingY1 = pREy-2.5;
            var stingX2 = pREx+15;
            var stingY2 = pREy+2.5;
            $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
        }
    }
    
};

BPM.connetDragged = function($this, conDLeft, conDTop) {   //определение присоединять ли перетянутый елемент
    var arrArr= $this.attr('d');
    arrArr = arrArr.split(' ');
    if (arrArr.length > 16) {
        if (conDLeft+5 >= arrArr[4] && conDLeft-5 <= arrArr[4] && conDTop+5 >= arrArr[5] && conDTop-5 <= arrArr[5] ||
            conDLeft+5 >= arrArr[1] && conDLeft-5 <= arrArr[1] && conDTop < arrArr[2] && conDTop > arrArr[5] ||
            conDLeft+5 >= arrArr[1] && conDLeft-5 <= arrArr[1] && conDTop < arrArr[5] && conDTop > arrArr[2] ||
            conDTop+5 >= arrArr[2] && conDTop-5 <= arrArr[2] && conDLeft < arrArr[1] && conDLeft > arrArr[4] ||
            conDTop+5 >= arrArr[2] && conDTop-5 <= arrArr[2] && conDLeft < arrArr[4] && conDLeft > arrArr[1] ||
            conDLeft+5 >= arrArr[4] && conDLeft-5 <= arrArr[4] && conDTop < arrArr[5] && conDTop > arrArr[8] ||
            conDLeft+5 >= arrArr[4] && conDLeft-5 <= arrArr[4] && conDTop < arrArr[8] && conDTop > arrArr[5] ||
            conDTop+5 >= arrArr[5] && conDTop-5 <= arrArr[5] && conDLeft < arrArr[4] && conDLeft > arrArr[7] ||
            conDTop+5 >= arrArr[5] && conDTop-5 <= arrArr[5] && conDLeft < arrArr[7] && conDLeft > arrArr[4] ) {
            BPM.separateArrow($this);
        }
    } else {
        if (conDLeft+5 >= arrArr[1] && conDLeft-5 <= arrArr[1] && conDTop < arrArr[2] && conDTop > arrArr[5] ||
            conDLeft+5 >= arrArr[1] && conDLeft-5 <= arrArr[1] && conDTop < arrArr[5] && conDTop > arrArr[2] ||
            conDTop+5 >= arrArr[2] && conDTop-5 <= arrArr[2] && conDLeft < arrArr[1] && conDLeft > arrArr[4] ||
            conDTop+5 >= arrArr[2] && conDTop-5 <= arrArr[2] && conDLeft < arrArr[4] && conDLeft > arrArr[1] ) {
            BPM.separateArrow($this);
        }
    }
};

BPM.separateArrow = function($this) {  //разделение и переопредиление стрелок при присоединении нового елемента
    var $clonew = $this.clone(true).attr('arr-end', $('div.bpm_operator.condrag').attr('el-num')+'').insertAfter($this);
    $this.attr('arr-begin', $('div.bpm_operator.condrag').attr('el-num')+'')
    .attr('stroke', $('div.bpm_operator.condrag .bpm_body').css('background-color')+'');
    BPM.recount($clonew);
    BPM.recount($this);
};

BPM.dragInit = function() {
    var positionNull = '0px0px';
    $( 'div.bpm_operator' ).draggable({ containment: '#content_container',
    drag: function( event, ui ) {     //события при перетягивании елемента
        /*var $dragedEl = $('div.bpm_operator.ui-draggable-dragging');
        $dragedEl.addClass('condrag');
        var positionChek = $dragedEl.css('left') + $dragedEl.css('top');
        if (positionNull != positionChek) {
            $('div.bpm_operator.marged').each(function(){
                var marLeft = $(this).css('left');
                marLeft = parseFloat(marLeft) - 163;
                $(this).css('left', +marLeft+'px').removeClass('marged');       
            });                 
            var compCooTop = $dragedEl.offset().top;
            var compCooLeft = $dragedEl.offset().left;
            $('div.bpm_operator').not('.ui-draggable-dragging').each(function(){                
                if ($(this).offset().top-20 <= compCooTop && $(this).offset().top+20 >= compCooTop && $(this).offset().left-20 <= compCooLeft && $(this).offset().left+20 >= compCooLeft) {                 
                    $('div.bpm_operator').not('.ui-draggable-dragging').each(function(){
                        var marElemTop = $(this).offset().top;
                        var marElemLeft = $(this).offset().left;
                        if (marElemTop == compCooTop && marElemLeft+10 >= compCooLeft) {
                            var marLeft = $(this).css('left');
                            if (marLeft == 'auto') {
                                $(this).addClass('marged').css('left', '163px');
                            } else {
                                marLeft = parseFloat(marLeft) + 163;
                                $(this).addClass('marged').css('left', +marLeft+'px');
                            }
                        }
                    });
                }
            });
            $('svg.arrows path.arrow').each(function(){
                var $this = $(this);
                BPM.recount($this);
            });
            positionNull = positionChek;
        }*/
    },
    stop: function( event, ui ) {
/*
        $('div.bpm_operator.marged').removeClass('marged');   //события после остановки елемента
        var $conDrag = $('div.bpm_operator.condrag .bpm_body');
        var conDTop = $conDrag.offset().top - $('svg.arrows').offset().top + $conDrag.height()/2;
        var conDLeft = $conDrag.offset().left - $('svg.arrows').offset().left + $conDrag.width()/2;
        $('svg.arrows path.arrow').each(function(){
            var $this = $(this);
            BPM.connetDragged($this, conDLeft, conDTop);
        });
        $('div.bpm_operator.condrag').removeClass('condrag');*/
        
    } });
};


  $(document).ready(function() {

    //запуск UI-draggable
    BPM.dragInit();

    //первый запуск, структура не построена, только (начало) и (конец)
    BPM.arrowDraw();

  });
</script>

            
            <!--a href="#">
                <svg width="18" height="18">
                    <path d="M1 17 L17 1 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M17 1 L11 4 L14 7 Z" stroke="#000000" fill="#000000"></path>
                </svg>
            </a-->
    <!--
    <div class="bpm_stroke">
        <svg width="70" height="70">
            <rect x="20" y="11" fill="#FFFFFF" stroke="#000000" width="26" height="42"></rect>    
            <rect x="5" y="1" fill="#FFFFFF" stroke="#000000" width="54" height="40"></rect>    
            <rect x="9" y="5" fill="#FFFFFF" stroke="#000000" width="46" height="31"></rect>            
            <path fill="#ffffff" stroke="#000000" d=" M63 60  H1  v-15  h63  V58  z"></path>   
            <path fill="#ffffff" stroke="#000000" d=" M40 50 h20 "></path>
        </svg>
    </div>
    <div class="bpm_stroke">
        <svg width="80" height="20">
            <path d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>  
    <div class="bpm_stroke">
        <svg width="80" height="20">
            <path d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>
    <div class="bpm_stroke">
        <svg width="80" height="20">
            <path d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>
    <div class="bpm_stroke">
        <svg width="80" height="20">
            <path d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>
    <div class="bpm_stroke">
        <svg width="80" height="20">
            <path d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 11 L60 7 L60 15 Z" stroke="#000000" fill="#000000"></path>
        </svg>
    </div>
    <div class="bpm_stroke hidden">
        <svg width="50" height="50">
            <circle cx="24" cy="24" r="20" stroke="black" stroke-width="4" fill="rgba(0,0,0,0)" />
        </svg>
    </div>
    <div class="bpm_stroke hidden">
        <svg width="50" height="50">
            <rect x="4" y="4" width="40" height="40" style="fill:rgba(0,0,0,0);stroke-width:4;stroke:rgb(0,0,0)" />
        </svg>
    </div>
    <div class="bpm_stroke hidden">
        <svg width="50" height="50">
            <rect x="4" y="4" rx="10" ry="10" width="40" height="40" style="fill:rgba(0,0,0,0);stroke:black;stroke-width:4;" />
        </svg>
    </div>
    -->