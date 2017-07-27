import React, { Component } from 'react';
import MenuBar from './_components/MenuBar';


class App extends Component {
    render () {
        return (
            <div>
                <MenuBar/>
                <h1 className="text-center">
                    Bienvenue!
                </h1>
            </div>
        )
    }
}

export default App;