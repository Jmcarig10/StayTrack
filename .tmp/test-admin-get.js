const n = await figma.getNodeByIdAsync('284:5601');
return JSON.stringify({ok: !!n, name: n && n.name});
