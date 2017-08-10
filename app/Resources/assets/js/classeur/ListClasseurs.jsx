import React, { Component } from 'react'
import Moment from 'moment';
import ClasseursRow from './ClasseursRow'

const styles = {
    progressbar: {
        width: '75%'
    }
}

class ListClasseurs extends Component {

    constructor(props) {
        super(props);
        this.state = {classeurs: null};
    }

    componentDidMount() {
        fetch(Routing.generate('list_classeur_api'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => {
                this.setState({classeurs : json})
                $('#classeurRow').foundation();
            });
    }

    render(){
        Moment.locale('fr')
        const classeurs = this.state.classeurs

        return (

            <div className="grid-x grid-margin-x grid-padding-x align-center-middle">
                <div className="cell medium-12 head-list-classeurs">
                    <div className="grid-x">
                        <div className="cell medium-12">
                            <h2>classeur(s) à signer</h2>
                        </div>
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x align-middle">
                        <div className="cell medium-12 list-classeurs">
                            <div className="grid-x grid-padding-x tri-classeurs">
                                <div className="cell medium-2">
                                    trier
                                    <button className="button arrow-down" type="button">&nbsp;</button>
                                    <button className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-3">
                                    <button className="button arrow-down" type="button">&nbsp;</button>
                                    <button className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2">
                                    <button className="button arrow-down" type="button">&nbsp;</button>
                                    <button className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2">
                                    <button className="button arrow-down" type="button">&nbsp;</button>
                                    <button className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2"></div>
                                <div className="cell medium-1 text-center">
                                    <input type="checkbox" />
                                </div>
                            </div>

                            <div id="classeurRow">
                                { classeurs ? (
                                    classeurs.map(classeur =>
                                        <ClasseursRow classeur={classeur} key={classeur.id} />
                                    )
                                ) : (<div>Chargement...</div>)
                                }
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        )
    }
}

export default ListClasseurs