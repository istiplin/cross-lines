class NumbersMenu{
    constructor(field){
        this._field = field;
    }
    
    get html(){
        if (this._html!=null)
            return this._html;
        
        let html="";
        html+="<table class='numbers-menu'>";
        for(var y=0; y<10; y++){
            html+="<tr>";
            for(var x=0; x<10; x++){
                let num = y*10+x;
                html+="<td class='number' data-number="+num+">";
                html+=num;
                html+="</td>";
            }
            html+="</tr>";
        }
        html+="</table>";

        return this._html = html;
    }
    
    get elem(){
        if (this._elem!=null)
            return this._elem;

        let elem = $(this._field.elem).find('.numbers-menu');;
        $(elem).find('.number').click(this.onClick.bind(this));
        return this._elem = elem;
    }
    
    onClick(e){
        if (this.currNumberLength == $(e.target).data('number')){
            this.elem.hide();
            return;
        }

        this.currNumberLength = $(e.target).data('number');
        this._field[this.currNumsName].changeNumsList(
                                                this.currLineInd,
                                                this.currNumberInd,
                                                this.currNumberLength
                                            );
        this._field[this.currNumsName].redraw();
        this._field.cells.redraw();
        this.elem.hide();
    }
}