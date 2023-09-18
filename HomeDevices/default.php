<!DOCTYPE html>
<html lang="en">
 
<head>
  <title>Gurjant's Home Control Panel</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="initial-scale=1, width=device-width" />
  <script src="https://unpkg.com/react@latest/umd/react.development.js" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/react-dom@latest/umd/react-dom.development.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/antd/4.18.5/antd.min.js"></script>
  <script src="https://unpkg.com/babel-standalone@latest/babel.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ant-design-icons/4.7.0/index.umd.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/4.18.5/antd.min.css" />
  <style>
    .ant-list-item-meta-title {
      font-size: 18px;
      margin-bottom: -6px !important;
    }
    .ant-list-item-meta-description {
      font-size: 15px;
    }
    h3 {
      font-weight: normal;
      text-align: center;
      padding-top: 20px;
    }
    .ant-list-item-meta-avatar {
      height: 45px;
      padding-top: 5px;
      padding-left: 10px;
    }
  </style>
</head>
 
<body>
  <div id="root"></div>
  <script type="text/babel">
 
    const TINXY_API_KEY = 'b5b9afde332e8ec4d5e40aa9e3e975dbf3e7251b';
    const TINXY_BASE_URL = 'https://backend.tinxy.in/v2/';
    const ICONS_BASE_URL = 'https://img.icons8.com/';
 
    const { Row, Col, Divider, List, Avatar, Switch, Typography } = antd;
    const { CloseOutlined, CheckOutlined, WifiOutlined } = icons;
 
    function get_Logo(str) {
      switch (true) {
        case /.*fan.*/i.test(str):
          return ICONS_BASE_URL + "ios/50/000000/fan-speed--v2.png";
        case /.*tube.*/i.test(str):
        case /.*light.*/i.test(str):
        case /.*bulb.*/i.test(str):
          return ICONS_BASE_URL + "wired/64/000000/light-automation.png";
        default:
          return ICONS_BASE_URL + "external-parzival-1997-detailed-outline-parzival-1997/64/000000/external-home-control-technology-in-daily-life-parzival-1997-detailed-outline-parzival-1997.png";
      }
    }
 
    function App() {
      const [error, setError] = React.useState(null);
      const [items, setItems] = React.useState([]);
      const [level, setLevel] = React.useState(0);

      React.useEffect(() => {
        fetch(TINXY_BASE_URL + "devices/", {
          headers: { 'Authorization': 'Bearer ' + TINXY_API_KEY }
        }).then(res => res.json()).then(async (dv) => {
          dv.map(async (item) => {
            item.devices.map(async (dt, i) => {

              fetch(`${TINXY_BASE_URL}devices/${item._id}/state?deviceNumber=${i + 1}`, {
                headers: { 'Authorization': 'Bearer ' + TINXY_API_KEY }
              }).then(res => res.json()).then((ds) => {

                setItems(prevState => [...prevState, {
                  'device_group': item.name,
                  'device_g_id': item._id,
                  'device_index': i + 1,
                  'device_name': item.devices[i],
                  'device_type': item.deviceTypes[i],
                  'device_state': ds.state == "ON" ? true : false,
                  'device_brightness': ds.brightness,
                  'device_image': get_Logo(item.deviceTypes[i]),
                  'item_loading': false
                }]);
              })
            })
            
          })
        },
          (error) => {
            setError(error);
          }
        )
      }, []);
 
      function toggleState(item, itemIndex, isLoading, deviceState) {
        const data = [...items];
        data[itemIndex].item_loading = isLoading
        data[itemIndex].device_state = deviceState
        setItems(data);
      }
      
      function updateBrigtness(value, id) {
        const data = [...items];
        console.log(value)
        for(var i=0; i<data.length; i++) {
          if (data[i].device_g_id == id) {
            if (value == "99" && data[i].device_brightness != "100"){
              value = "100";
            }else if (value == "99" && data[i].device_brightness == "100"){
              value = "66";
            }
            data[i].device_brightness = value
          }
        }
        // data[itemIndex].device_brightness = value
        setItems(data);
      }
      
      return (
        <div>
          <Row justify="center">
            <Col span={6} justify="center">
              <Typography.Title level={3} type="primary">Gurjant's Home Control Panel</Typography.Title>
            </Col>
            </Row>
            

            <Row justify="center">
              <Col span={6}>
                <List
                itemLayout="horizontal"
                dataSource={items}
                renderItem={item => (
                  <List.Item>
                    <List.Item.Meta avatar={<Avatar src={item.device_image} />}
                    title={item.device_name} description={item.device_group} />
                    
                    
                    {item.device_name == 'Fan'? <input type="number" min="33" max="100" step="33" value={item.device_brightness}
                    onChange={e => updateBrigtness(e.target.value, item.device_g_id)}/> : <input type="hidden"/>}
                    {item.device_name == 'Fan'?
                  <Switch
                  checkedChildren={<CheckOutlined />} unCheckedChildren={<CloseOutlined />}
                  checked={item.device_state} loading={item.item_loading}
                  onClick={() => {
                    
                    const itemIndex = items.findIndex((dt) =>
                    dt.device_g_id == item.device_g_id &&
                    dt.device_index == item.device_index);
 
                        toggleState(item, itemIndex, true, !item.device_state)
 
                        fetch(`${TINXY_BASE_URL}devices/${item.device_g_id}/toggle`, {
                          method: "post",
                          crossDomain: true,
                          headers: { "Content-Type": "application/json", 'Authorization': 'Bearer ' + TINXY_API_KEY },
                          body: JSON.stringify(
                            { request: 
                              { state: item.device_state ? 1 : 0 , 
                                brightness:  item.device_state ? parseInt(item.device_brightness) :0
                              }, 
                              deviceNumber: item.device_index 
                            })
                          }).then(res => res.json()).then((dt) => {
                            toggleState(item, itemIndex, false, item.device_state)
                          }).catch((e) => {
                            toggleState(item, itemIndex, false, !item.device_state)
                            setError(e);
                          });
                        }} />
                        
                        : 
                    <Switch
                      checkedChildren={<CheckOutlined />} unCheckedChildren={<CloseOutlined />}
                      checked={item.device_state} loading={item.item_loading}
                      onClick={() => {
 
                        const itemIndex = items.findIndex((dt) =>
                          dt.device_g_id == item.device_g_id &&
                          dt.device_index == item.device_index);
 
                        toggleState(item, itemIndex, true, !item.device_state)
 
                        fetch(`${TINXY_BASE_URL}devices/${item.device_g_id}/toggle`, {
                          method: "post",
                          crossDomain: true,
                          headers: { "Content-Type": "application/json", 'Authorization': 'Bearer ' + TINXY_API_KEY },
                          body: JSON.stringify(
                            { request: 
                              { state: item.device_state ? 1 : 0 , 
                              }, 
                              deviceNumber: item.device_index 
                            })
                        }).then(res => res.json()).then((dt) => {
                          toggleState(item, itemIndex, false, item.device_state)
                        }).catch((e) => {
                          toggleState(item, itemIndex, false, !item.device_state)
                          setError(e);
                        });
                      }} />
                    }
                        
                  </List.Item>
                )}
              />
            </Col>
          </Row>
        </div>
      );
    }
 
    ReactDOM.render(
      <App />,
      document.querySelector('#root'),
    );
  </script>
</body>
</html>
