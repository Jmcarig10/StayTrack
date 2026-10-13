const keys = ['lucide:house','lucide:bell','lucide:wrench','lucide:check'];
const out=[];
for (const key of keys) {
 const n=figma.currentPage.findAll(x=>x.name===key)[0];
 out.push({key,desc:n? n.findAll(x=>x.type==='VECTOR'||x.type==='GROUP').map(x=>({id:x.id,type:x.type,fills:'fills' in x?x.fills:null,strokes:'strokes' in x?x.strokes:null})):[]});
}
return out;
