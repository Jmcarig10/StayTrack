const out=[];
for(const n of figma.currentPage.children){if(n.type==='FRAME'&&/Admin/i.test(n.name)){out.push({id:n.id,name:n.name,width:n.width,height:n.height,reactions:n.reactions?.length??0,children:n.children.length});}}
return out;
