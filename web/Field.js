class Field{
    constructor(id,horNumsList,vertNumsList,cellsList) {
        this._step = 12;
        
        this.elem = document.getElementById(id);
        this.elem.innerHTML = this.elemInnerHtml;
        

        this.horNums = new HorizontalNumbers(this,horNumsList);
        this.vertNums = new VerticalNumbers(this,vertNumsList);
        this.cells = new Cells(this);
        
        this.elem.onclick = this.onClick.bind(this);
        
        this.currNumsName = '';
    }
    
    get numbersMenu(){
        if (!this._numbersMenu){
            this._numbersMenu = new NumbersMenu(this);
        }
        return this._numbersMenu;
    }
	
    get elemInnerHtml(){
        let html="";
        html+= "<table>";
        html+=  "<tr>";
        html+=   "<td></td>";
        html+=   "<td><canvas width=0 height=0 class='vertNums'></canvas></td>";
        html+=  "</tr>";
        html+=  "<tr>";
        html+=   "<td><canvas width=0 height=0 class='horNums'></canvas></td>";
        html+=   "<td><canvas width=0 height=0 class='cells'></canvas></td>";
        html+=  "</tr>";
        html+= "</table>";
        html+= this.numbersMenu.html;
        return html;
    }

    get horNumsList(){
        return this.horNums._numsList
    }

    get vertNumsList(){
        return this.vertNums._numsList
    }
	
    get cellsList(){
        return this.cells.list;
    }

    get step(){
        return this._step;
    }
	
    redraw(list){
        this.horNums.redraw();
        this.vertNums.redraw();
        this.cells.redraw(list);
    }

    draw(list){
        this.horNums.draw();
        this.vertNums.draw();
        this.cells.draw(list);
    }

    onClick(e){
        this.numbersMenu.elem.hide();
        if (e.target.className!='horNums' && e.target.className!='vertNums')
            return;
        
        this[e.target.className].onClick(e);
    }
	
    get horNumsSum(){
        return this.horNums.sum;
    }

    get vertNumsSum(){
        return this.vertNums.sum;
    }
}