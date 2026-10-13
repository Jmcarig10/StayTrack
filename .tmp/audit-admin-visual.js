const ids = ['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const names = ['NEW FLOW / Admin HD / Maintenance / Index','NEW FLOW / Admin HD / Bills / Index','NEW FLOW / Admin HD / Payments / Index','NEW FLOW / Admin HD / Profile','NEW FLOW / Admin HD / Notifications / Panel'];
const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);if(n)out.push({name:n.name,undefined:n.findAll(x=>x.type==='TEXT'&&x.characters==='undefined').length,squareIcons:n.findAll(x=>x.type==='RECTANGLE'&&/^lucide:/.test(x.name||'')).length});}
for(const name of names){const n=figma.currentPage.children.find(x=>x.name===name);if(n)out.push({name:n.name,undefined:n.findAll(x=>x.type==='TEXT'&&x.characters==='undefined').length,squareIcons:n.findAll(x=>x.type==='RECTANGLE'&&/^lucide:/.test(x.name||'')).length});}
return out;
