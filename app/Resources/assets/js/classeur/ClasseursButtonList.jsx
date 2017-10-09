import React, { Component } from 'react'
import { object, array }from 'prop-types'

class ClasseursButtonList extends Component {

    render () {

        const { classeur, classeurs } = this.props

        return (
            (classeur || classeurs) &&
            <div className="grid-x">
                {
                    (
                        classeur && classeur.validable
                        || classeurs && !classeurs.filter(classeur => !classeur.validable).length
                    ) &&
                    <div className="cell auto"><a href="#" className="btn-valid"></a></div>
                }

                {
                    (
                        classeur && classeur.signable_and_last_validant
                        || classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length
                    ) &&
                    <div className="cell auto"><a href="#" className="btn-sign"></a></div>
                }

                <div className="cell auto"><a href="#" className="btn-revert"></a></div>
                {
                    (
                        classeur && classeur.status === 3
                        || classeurs && !classeurs.filter(classeur => classeur.status !== 3).length > 0
                    ) &&
                    <div className="cell auto"><a href="#" className="btn-refus"></a></div>
                }
                <div className="cell auto"><a href="#" className="btn-comment"></a></div>
            </div>
        )
    }
}

ClasseursButtonList.PropTypes = {
    classeur: object,
    classeurs: array
}

export default ClasseursButtonList