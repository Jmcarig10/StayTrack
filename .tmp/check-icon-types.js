await figma.currentPage.loadAsync();const out=[];for(const n of figma.currentPage.findAll(x=>/pencil|trash|plus|eye|search/.test(x.name||''))){out.push({id:n.id,name:n.name,type:n.type,w:n.width,h:n.height,parent:n.parent?.name});}
return out.slice(0,80);
