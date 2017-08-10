import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { Link } from 'react-router-dom'

class Classeur extends Component {

    constructor (props) {
        super(props);
        this.state = { classeur: null}
    }

    getClasseur (classeurId) {
        fetch(Routing.generate('get_classeur_api', {id: classeurId}) , { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeur: json})
            )
    }

    componentDidMount () {
        this.getClasseur(this.props.classeurId)
    }

    componentWillReceiveProps (nextProps) {
        if (nextProps.classeurId !== this.props.classeurId) {
            this.getClasseur(nextProps.classeurId)
        }
    }

    render () {
        const classeur = this.state.classeur
        return (
            <div>
                <Link to={"/classeur/list"}>Retour liste des classeurs</Link>
                Coucou classeur {this.props.classeurId} #id
                { classeur ? (
                    <div>
                        Classeur : {classeur.id} {classeur.nom}
                    </div>
                    )
                    : "Chargement du classeur"
                }
            </div>
        )
    }
}

Classeur.PropTypes = {
    classeurId: PropTypes.number.isRequired,
}

export default Classeur