const get=async id=>await figma.getNodeByIdAsync(id);const out=[];
for(const [a,b] of [['170:1386','166:666'],['170:1390','166:755'],['170:1394','170:844'],['170:1398','170:938'],['170:1407','170:1037']]){const n=await get(a),to=await get(b);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);out.push({from:a,to:b,ok:true});}catch(e){out.push({from:a,to:b,ok:false,reason:String(e).slice(0,260)})}}
return out;
