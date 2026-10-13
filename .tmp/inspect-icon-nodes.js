const keys = ['lucide:house','lucide:bell','lucide:building-2','lucide:wrench','lucide:check'];
const out=[];
for (const key of keys) {
 const n=figma.currentPage.findAll(x=>x.name===key)[0];
 out.push(n?{key,id:n.id,type:n.type,w:n.width,h:n.height,children:n.children?.length||0,childTypes:n.children?.map(c=>c.type),fills:n.fills}: {key,missing:true});
}
return out;
