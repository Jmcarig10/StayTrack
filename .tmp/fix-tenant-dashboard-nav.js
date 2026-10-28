const pairs=[
  ['283:1921','282:1262'],
  ['283:1927','282:1371'],
  ['283:1933','282:1486'],
  ['283:1939','282:1596'],
  ['283:1945','278:3503'],
  ['283:1950','278:3761'],
  ['283:1963','278:3890']
];
const out=[];
for(const [a,b] of pairs){
  try{
    const f=await figma.getNodeByIdAsync(a), t=await figma.getNodeByIdAsync(b);
    await f.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:t.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);
    out.push({a,b,ok:true});
  }catch(e){out.push({a,b,ok:false,error:String(e)});}
}
return out;
