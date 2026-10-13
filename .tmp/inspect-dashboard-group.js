const g=await figma.getNodeByIdAsync('365:6877');return {name:g.name,type:g.type,children:g.children?.map(c=>({id:c.id,name:c.name,type:c.type,x:c.x,y:c.y,width:c.width,height:c.height}))};
