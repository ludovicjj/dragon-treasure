import React from 'react';

const Field = React.forwardRef(function (props, ref) {
    const {name, children} = props

    return <div className="mb-3">
        <label htmlFor={name} className="form-label">{children}</label>
        <input type="text" name={name} id={name} ref={ref} className="form-control"/>
    </div>
})

export default class Home extends React.Component {
    constructor(props) {
        super(props)
        this.handleSubmit = this.handleSubmit.bind(this);
        this.email = React.createRef()
        this.password = React.createRef()
    }

    async handleSubmit(e) {
        e.preventDefault();
        const email = this.email.current
        const password = this.password.current

        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
        })
    }

    render () {
        return <form onSubmit={this.handleSubmit}>
            <Field name="email" ref={this.email}>Email</Field>
            <Field name="password" ref={this.password}>Password</Field>
            <button className="btn btn-primary">Submit</button>
        </form>;
    }
}