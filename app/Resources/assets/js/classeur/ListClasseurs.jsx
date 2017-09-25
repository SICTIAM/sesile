import React, { Component } from 'react'
import PropTypes from 'prop-types'
import ClasseursRow from './ClasseursRow'


class ListClasseurs extends Component {

    constructor(props) {
        super(props);
        this.state = {
            classeurs: null,
            sort: "id",
            order: "DESC",
            limit: 10,
            start: 0,
            checkedAll: false,
            userId: this.props.userId
        }
    }

    componentDidMount() {
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.state.userId)
    }

    listClasseurs(sort, order, limit, start, userId) {

        fetch(Routing.generate('sesile_classeur_classeurapi_list', {sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => {
                this.setState({classeurs : json})
                $('#classeurRow').foundation();
            })
            .then(() => {
                let classeurs = this.state.classeurs.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                this.setState({classeurs})
            })
    }


    checkAllClasseurs() {
        const classeurs = this.state.classeurs
        const newCheckAll = !this.state.checkedAll
        this.setState({checkedAll: newCheckAll})
        classeurs.map(classeur => classeur.checked = newCheckAll)
        this.setState({classeurs})
    }

    checkClasseur(event) {
        const target = event.target
        const classeurs = this.state.classeurs
        classeurs[classeurs.findIndex(classeur => classeur.id == target.id)].checked = (target.checked)
        this.setState({classeurs})
    }

    render(){
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
                                    <button onClick={() => this.listClasseurs('user.nom', 'ASC')} className="button arrow-down" type="button">&nbsp;</button>
                                    <button onClick={() => this.listClasseurs('user.nom', 'DESC')} className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-3">
                                    <button onClick={() => this.listClasseurs('nom', 'ASC')} className="button arrow-down" type="button">&nbsp;</button>
                                    <button onClick={() => this.listClasseurs('nom', 'DESC')} className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2">
                                    <button onClick={() => this.listClasseurs('creation', 'ASC')} className="button arrow-down" type="button">&nbsp;</button>
                                    <button onClick={() => this.listClasseurs('creation', 'DESC')} className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2">
                                    <button onClick={() => this.listClasseurs('validation', 'ASC')} className="button arrow-down" type="button">&nbsp;</button>
                                    <button onClick={() => this.listClasseurs('validation', 'DESC')} className="button arrow-up" type="button">&nbsp;</button>
                                </div>
                                <div className="cell medium-2"></div>
                                <div className="cell medium-1 text-center">
                                    <input value={this.state.checkedAll} onClick={() => this.checkAllClasseurs()} type="checkbox" />
                                </div>
                            </div>

                            <div id="classeurRow">
                                { classeurs ? (
                                    classeurs.map(classeur =>
                                        <ClasseursRow classeur={classeur} key={classeur.id} checkClasseur={this.checkClasseur} />
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

ListClasseurs.PropTypes = {
    userId: PropTypes.number
}

export default ListClasseurs